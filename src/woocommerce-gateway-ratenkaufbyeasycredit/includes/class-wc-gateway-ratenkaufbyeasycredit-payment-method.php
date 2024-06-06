<?php
use Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType;
use Automattic\WooCommerce\Blocks\Payments\PaymentResult;
use Automattic\WooCommerce\Blocks\Payments\PaymentContext;

defined( 'ABSPATH' ) || exit;

class WC_Gateway_Ratenkaufbyeasycredit_Payment_Method extends AbstractPaymentMethodType {

    protected $plugin_file = null;
    protected $name = 'easycredit';

    public function __construct($plugin_file) {
        $this->plugin_file = $plugin_file;
    }

    public function initialize()
    {
        $this->settings = get_option('woocommerce_easycredit_settings', []);
    }

    public function is_active()
    {
        return true;
    }

    public function get_payment_method_script_handles()
    {
        /*
        wp_register_script(
            'stripe',
            'https://js.stripe.com/v3/',
            [],
            '3.0',
            true
        );
        */

        // if (WC_Stripe_Feature_Flags::is_upe_checkout_enabled()) {
            $this->register_payment_method_script_handles();
        // } else {
        //    $this->register_legacy_payment_method_script_handles();
        // }

        return ['wc-easycredit-blocks'];
    }

    public function get_payment_method_script_handles_for_admin()
    {
        return $this->get_payment_method_script_handles();
    }

    /**
     * Registers the UPE JS scripts.
     */
    private function register_payment_method_script_handles()
    {
        $dir = 'build/client/blocks';
        $asset_path   = plugin_dir_path($this->plugin_file) . $dir . '/index.asset.php';

        $dependencies = [];
        $version = '1.0';

        require $asset_path;
        /*
        wp_enqueue_style(
            'wc-stripe-blocks-checkout-style',
            $dir . '/build/upe_blocks.css',
            [],
            $version
        );
        */

        wp_register_script(
            'wc-easycredit-blocks',
            plugin_dir_url($this->plugin_file) . $dir . '/index.js',
            $dependencies,
            $version,
            true
        );
        wp_set_script_translations(
            'wc-easycredit-blocks',
            'wc-easycredit'
        );
    }

    public function get_payment_method_data()
    {
        return [];
    }
}