<?php
class WC_Gateway_Ratenkaufbyeasycredit_Widget_Cart extends WC_Gateway_Ratenkaufbyeasycredit_Widget {

    protected function should_be_displayed() {

        if (!is_cart()
            || $this->gateway->get_option('cart_widget_enabled') != 'yes'
            || trim($this->gateway->get_option('api_key')) == ''
            || WC()->cart->total == 0
        ) {
            return false;
        }
        return true;
    }

    public function add_meta_tags( $array ) {

        $cartTotal = WC()->cart->total;
        if ($cartTotal > 0) {
			echo '<meta name="easycredit-widget-selector" content="'.$this->gateway->get_option('cart_widget_selector').'">';
            echo '<meta name="easycredit-widget-price" content="'.$cartTotal.'">';
            echo '<meta name="easycredit-api-key" content="'.$this->gateway->get_option('api_key').'">';
        }
    }
}
