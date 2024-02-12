<?php
namespace Netzkollektiv\EasyCredit\Admin;

use Teambank\RatenkaufByEasyCreditApiV3\ApiException;
use Teambank\RatenkaufByEasyCreditApiV3\Model\CaptureRequest;
use Teambank\RatenkaufByEasyCreditApiV3\Model\ConstraintViolation;
use Teambank\RatenkaufByEasyCreditApiV3\Model\RefundRequest;

use Netzkollektiv\EasyCredit\Plugin;
use Netzkollektiv\EasyCredit\Integration;

class OrderManagement
{
    protected $_field = 'merchant-status';

    protected $plugin;

    protected $integration;

    public function __construct(
        Plugin $plugin,
        Integration $integration
    ) {
        $this->plugin = $plugin;
        $this->integration = $integration;

        add_action('manage_shop_order_posts_custom_column', [$this, 'add_order_column_content'], 20);
        add_action('woocommerce_admin_order_data_after_shipping_address', [$this, 'add_status_after_shipping_address'], 10, 1);
        add_action('admin_enqueue_scripts', [$this, 'require_transaction_manager']);
        add_action('add_meta_boxes', [$this, 'add_meta_boxes']);

        foreach (['shipped', 'refunded'] as $state) {
            if ($this->plugin->get_option('mark_' . $state) == 'yes') {
                $status = $this->plugin->get_option('mark_' . $state . '_status');
                $status = str_replace('wc-', '', $status);
                add_action('woocommerce_order_status_' . $status, [$this, 'mark_' . $state], 10, 2);
            }
        }
    }

    public function get_field()
    {
        return $this->plugin->id . '-' . $this->_field;
    }

    public function get_endpoint_vars()
    {
        return [
            'endpoints' => [
                'get' => get_rest_url(null, 'easycredit/v1/transaction?ids={transactionId}'),
                'list' => get_rest_url(null, 'easycredit/v1/transactions?ids={transactionId}'),
                'capture' => get_rest_url(null, 'easycredit/v1/capture?id={transactionId}'),
                'refund' => get_rest_url(null, 'easycredit/v1/refund?id={transactionId}'),
            ],
            'request_config' => [
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
            ],
        ];
    }

    public function require_transaction_manager()
    {
        wp_register_script('easycredit_transaction_manager', '');
        wp_enqueue_script('easycredit_transaction_manager');
        wp_add_inline_script('easycredit_transaction_manager', 'window.ratenkaufbyeasycreditOrderManagementConfig = ' . json_encode($this->get_endpoint_vars()));
    }

    public function get_transactions($transaction_id = null)
    {
        global $wpdb;

        $cond = ($transaction_id !== null) ? ' AND m.meta_value = "' . $transaction_id . '"' : '';
        $data = $wpdb->get_results(
            '
            SELECT m.meta_value as transaction_id, p.ID as post_id, m1.meta_value as transaction
            FROM  ' . $wpdb->posts . ' p 
            LEFT JOIN ' . $wpdb->postmeta . ' m ON m.post_id = p.ID AND m.meta_key = "' . Plugin::META_KEY_TRANSACTION_ID . '"
            LEFT JOIN ' . $wpdb->postmeta . ' m1 ON m1.post_id = p.ID AND m1.meta_key = "' . $this->get_field() . '"
            WHERE post_type = "shop_order" AND m.meta_key IS NOT NULL
            ' . $cond . ';',
            OBJECT_K
        );
        return $data;
    }

    public function get_transaction($order_id)
    {
        if ($order_id instanceof \WC_Order) {
            $order_id = $order_id->get_id();
        }

        $status = get_post_meta($order_id, $this->get_field(), true);
        return json_decode($status);
    }

    public function add_status_after_shipping_address($order)
    {
        $content = $this->get_order_status_icon($order);
        if ($content) {
            echo $content;
        }
    }

    public function add_order_column_content($column)
    {
        if ($column !== 'order_status') {
            return;
        }

        $order = $this->get_order();
        $content = $this->get_order_status_icon($order);
        if ($content) {
            echo $content;
        }
    }

    public function add_meta_boxes($post_type)
    {
        if ($post_type !== 'shop_order') {
            return false;
        }
        if (!$this->plugin->is_easycredit_method($this->get_order()->get_payment_method())) {
            return false;
        }

        add_meta_box(
            'easycredit-merchant-status',
            __('Order Management', 'wc-easycredit'),
            [$this, 'add_order_management_meta_box'],
            'shop_order',
            'side',
            'core'
        );
    }

    public function add_order_management_meta_box($post_id = null)
    {
        $order = $this->get_order($post_id);
        ?>
            <easycredit-merchant-manager 
                tx-id="<?php echo $order->get_meta(Plugin::META_KEY_TRANSACTION_ID); ?>" 
                date="<?php echo $order->get_date_created()->format('Y-m-d'); ?>"    
            />
        <?php
    }

    public function get_order_status_icon($order)
    {
        if (!$this->plugin->is_easycredit_method($order->get_payment_method())) {
            return;
        }

        return '<easycredit-merchant-status-widget  
            tx-id="' . $order->get_meta(Plugin::META_KEY_TRANSACTION_ID) . '" 
            date="' . $order->get_date_created()->format('Y-m-d') . '" 
        />';
    }

    public function mark_shipped($order_id, $order)
    {
        if (!$this->plugin->is_easycredit_method($order->get_payment_method())) {
            return;
        }

        try {
            try {
                $txId = $order->get_transaction_id();
                if (!$txId) {
                    throw new \Exception(__('The transaction id of this transaction is not available. This usually happens if the webhook which confirms the transaction is not working properly.', 'wc-easycredit'));
                }

                $this->integration->merchant_client()
                    ->apiMerchantV3TransactionTransactionIdCapturePost(
                        $txId,
                        new CaptureRequest([])
                    );
                $order->add_order_note(__('Shipment automatically set in easyCredit payment', 'wc-easycredit'));
            } catch (ApiException $e) {
                if ($e->getResponseObject() instanceof ConstraintViolation) {
                    $error = 'easyCredit: ';
                    foreach ($e->getResponseObject()->getViolations() as $violation) {
                        $error .= $violation->getMessage();
                    }
                    throw new \Exception($error);
                }
                throw $e;
            }
        } catch (\Exception $e) {
            $order->add_order_note(sprintf(__('Shipment update failed with message: %s', 'wc-easycredit'), $e->getMessage()));
        }
    }

    public function mark_refunded($order_id, $order)
    {
        if (!$this->plugin->is_easycredit_method($order->get_payment_method())) {
            return;
        }
        
        try {
            try {
                $txId = $order->get_transaction_id();
                if (!$txId) {
                    throw new \Exception(__('The transaction id of this transaction is not available. This usually happens if the webhook which confirms the transaction is not working properly.', 'wc-easycredit'));
                }

                $this->integration->merchant_client()
                    ->apiMerchantV3TransactionTransactionIdRefundPost(
                        $txId,
                        new RefundRequest([
                            'value' => $order->get_total(),
                        ])
                    );
                $order->add_order_note(__('Refund automatically set in easyCredit payment', 'wc-easycredit'));
            } catch (ApiException $e) {
                if ($e->getResponseObject() instanceof ConstraintViolation) {
                    $error = 'easyCredit: ';
                    foreach ($e->getResponseObject()->getViolations() as $violation) {
                        $error .= $violation->getMessage();
                    }
                    throw new \Exception($error);
                }
                throw $e;
            }
        } catch (\Exception $e) {
            $order->add_order_note(sprintf(__('Refund update failed with message: %s', 'wc-easycredit'), $e->getMessage()));
        }
    }

    protected function get_order($post_id = null)
    {
        if ($post_id === null) {
            global $post;
            $post_id = $post->ID;
        }
        return new \WC_Order($post_id);
    }
}
