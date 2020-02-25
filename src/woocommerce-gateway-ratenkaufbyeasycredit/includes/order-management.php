<?php
class WC_Gateway_Ratenkaufbyeasycredit_Order_Management {

    protected $_field = 'merchant-status';

    public function __construct($plugin) {
    	$this->plugin = $plugin;
    	$this->plugin_url = $plugin->plugin_url;
    	$this->gateway = $this->plugin->get_gateway();

        add_action( 'manage_shop_order_posts_custom_column',  array( $this, 'add_order_column_content'),20);
        add_action( 'add_meta_boxes', array($this, 'add_meta_boxes') );
        add_action( 'woocommerce_admin_order_data_after_shipping_address',  array( $this, 'add_status_after_shipping_address'), 10, 1);
        add_action( 'admin_enqueue_scripts', array( $this, 'require_transaction_manager') );
        add_action( 'admin_notices', array($this, 'bg_sync_transactions'));

        foreach (array('shipped','refunded') as $state) {
            if ($this->gateway->get_option('mark_'.$state) == 'yes') {
                $status = $this->gateway->get_option('mark_'.$state.'_status');
                $status = str_replace('wc-','',$status);

                add_action( 'woocommerce_order_status_'.$status, array($this, 'mark_'.$status),10,2);
            }
        }

        add_action('admin_head', array($this,'add_endpoint_vars'));
    }

    public function get_field() {
        return $this->gateway->id.'-'.$this->_field;
    }

    public function add_endpoint_vars() {
        $endpoints = array(
          'get' => get_rest_url(null, 'easycredit/v1/transaction'),
          'list' => get_rest_url(null, 'easycredit/v1/transactions'),
          'post' => get_rest_url(null, 'easycredit/v1/transaction')
       );
       echo "<script>window.ratenkaufbyeasycreditOrderManagementEndpoints = ".json_encode($endpoints).";</script>";
    }

    public function require_transaction_manager() {
        //wp_enqueue_style( 'wc_ratenkaufbyeasycredit_css',
        //    $this->plugin_url. 'assets/css/easycredit-backend.css' );

        wp_register_style( 'easycredit_transaction_manager', $this->plugin_url . '/assets/css/easycredit-backend.min.css', false, '1.0.0' );
        wp_enqueue_style( 'easycredit_transaction_manager' );
        wp_register_script( 'easycredit_transaction_manager', $this->plugin_url . '/assets/js/easycredit-backend.min.js', false, '1.0.0' );
        wp_enqueue_script( 'easycredit_transaction_manager' );

    }

    public function bg_sync_transactions() {
        $screen = get_current_screen();
        if ($screen->base == 'edit'
            && $screen->parent_base == 'woocommerce' 
            && $screen->post_type == 'shop_order'
        ) {
            $this->sync_transactions();
        }
    }

    public function sync_transactions() {
        try {
            $transactions = $this->gateway->get_merchant_client()
                ->searchTransactions();

            $ids = $this->get_transactions();
            foreach ($ids as $transaction_id => $entry) {
                foreach ($transactions as $transaction) {
                    if ($transaction->vorgangskennungFachlich == $transaction_id) {
                        update_post_meta($entry->post_id, $this->get_field(), json_encode($transaction));
                    }
                }
            }
        } catch (Exception $e) {
            error_log($e->getMessage());
        }
    }

    public function get_transactions($transaction_id = null) {
        global $wpdb;

        $cond = '';
        if ($transaction_id) {
            $cond = ($transaction_id !== null) ? ' AND m.meta_value = "'.$transaction_id.'"' : '';
        }

        $data = $wpdb->get_results('
            SELECT m.meta_value as transaction_id, p.ID as post_id, m1.meta_value as transaction
            FROM  wp_posts p 
            LEFT JOIN wp_postmeta m ON m.post_id = p.ID AND m.meta_key = "'.$this->gateway->id.'-transaction-id"
            LEFT JOIN wp_postmeta m1 ON m1.post_id = p.ID AND m1.meta_key = "'.$this->get_field().'"
            WHERE post_type = "shop_order" AND m.meta_key IS NOT NULL
            '.$cond.';', OBJECT_K
        );
        return $data;
    }

    public function get_transaction($order_id) {
        if ($order_id instanceof WC_Order) {
            $order_id = $order_id->get_id();
        }

        $status = get_post_meta($order_id, $this->get_field(), true);
        return json_decode($status);
    }

    public function add_status_after_shipping_address($order) {        
        if ($content = $this->get_order_status_icon($order)) {
            echo $content;
        }
    }

    public function add_order_column_content( $column ) {
        if ( 'order_status' !== $column ) {
            return;
        }

        global $post;
        $order = new WC_Order( $post->ID );

        if ($content = $this->get_order_status_icon($order)) {
            echo $content;
        }
    }

    public function add_meta_boxes()
    {
        add_meta_box( 
            'easycredit-merchant-status', 
            __('Order Management','woocommerce-gateway-ratenkaufbyeasycredit'), 
            array( $this,'add_order_management_meta_box'), 
            'shop_order', 
            'side', 
            'core'
        );
    }

    public function add_order_management_meta_box($post_id = null)
    {
        if ($post_id === null) {
            global $post;
            $post_id = $post->ID;
        }
        $order = new WC_Order( $post_id );
        ?>
            <easycredit-tx-manager 
                id="<?php echo $order->get_meta($this->gateway->id.'-transaction-id'); ?>" 
                date="<?php echo $order->get_date_created()->format ('Y-m-d'); ?>"    
            />
        <?php
    }

    public function get_order_status_icon($order) {
        if ($order->get_payment_method() !== 'ratenkaufbyeasycredit') {
            return;
        }

        return '<easycredit-tx-status 
            id="'.$order->get_meta($this->gateway->id.'-transaction-id').'" 
            date="'.$order->get_date_created()->format ('Y-m-d').'" 
        />';
    }

    public function mark_shipped($order_id, $order) {

        try {
            $client = $this->gateway->get_merchant_client()
                ->confirmShipment($order->get_transaction_id());
            
            $order->add_order_note( __("Shipment automatically set in ratenkauf by easyCredit") );
        } catch (\Exception $e) {
            $order->add_order_note( __("Shipment update failed with message: %s", $e->getMessage()) );
        }
    }

    public function mark_refunded($order_id, $order) {

        try {
            $client = $this->gateway->get_merchant_client()
                ->cancelOrder(
                $order->get_transaction_id(), 
                'WIDERRUF_VOLLSTAENDIG',
                new DateTime()
            );

            $order->add_order_note( __("Refund automatically set in ratenkauf by easyCredit") );
        } catch (\Exception $e) {
            $order->add_order_note( __("Refund update failed with message: %s", $e->getMessage()) );
        }
    }
}
