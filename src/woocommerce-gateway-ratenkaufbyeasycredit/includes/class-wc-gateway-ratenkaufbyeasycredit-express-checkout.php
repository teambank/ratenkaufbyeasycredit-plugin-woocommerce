<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WC_Gateway_Ratenkaufbyeasycredit_Express_Checkout {

    public function __construct($plugin) {
        $this->plugin = $plugin;
        $this->plugin_url = $plugin->plugin_url;
        $this->gateway = $this->plugin->get_gateway();

        add_action ( 'init', array($this, 'run'));
        add_action ( 'wp', array($this, 'init_buttons'));
    }

    public function run() {
        if ($this->is_express_action()) {
        	add_filter('woocommerce_add_to_cart_product_id', [$this, 'clear_cart']);
        	add_filter('woocommerce_add_to_cart_redirect', [$this, 'handle_express_redirect']);
        }
    }

    public function init_buttons() {
        if ($this->should_be_displayed_at_product()) {
            add_action('woocommerce_single_product_summary', [$this, 'add_button_at_product'], 30, 2);
        }
        if ($this->should_be_displayed_in_cart()) {
            add_action('woocommerce_proceed_to_checkout', [$this, 'add_button_in_cart']);
        }
    }

    public function is_express_action() {
        return isset($_REQUEST['easycredit-express']);
    }

    public function clear_cart($product_id) {
        WC()->cart->empty_cart();
        return $product_id;
    }

    public function handle_express_redirect($url) {
        wp_redirect(\esc_url_raw( \get_home_url(null, '/easycredit/express') ));
        exit;
    }

    public function add_button_at_product() {
        global $post;

        $product = new WC_Product( $post->ID );
        $amount = $product->get_price();

        if ($product->is_in_stock() && $product->get_price() > 199 && $product->get_price() <= 10000) {
            echo '<easycredit-express-button 
                webshop-id="'.$this->gateway->get_option('api_key').'"
                amount="'.$product->get_price().'"
            ></easycredit-express-button>';
        }
    }

    public function add_button_in_cart() {
        echo '<easycredit-express-button 
            webshop-id="'.$this->gateway->get_option('api_key').'"
            amount="'.WC()->cart->total.'"
            full-width
        ></easycredit-express-button>';
    }

    protected function should_be_displayed_at_product() {
        global $post;

        if (!isset($post->ID) ||
            $post->post_type != 'product' ||
            !is_product() ||
            $this->gateway->get_option('express_checkout_detail_enabled') != 'yes' ||
            trim($this->gateway->get_option('api_key')) == ''
        ) {
            return false;
        }
        return true;
    }

    protected function should_be_displayed_in_cart() {
        if ($this->gateway->get_option('express_checkout_cart_enabled') != 'yes' ||
            trim($this->gateway->get_option('api_key')) == '' ||
            WC()->cart->total === 0 ||
            !is_cart()
        ) {
            return false;
        }
        return true;
    }
}
