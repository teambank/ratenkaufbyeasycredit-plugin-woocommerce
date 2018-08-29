<?php
class WC_Gateway_Ratenkaufbyeasycredit_Widget_Product extends WC_Gateway_Ratenkaufbyeasycredit_Widget {

    protected function should_be_displayed() {
        global $post;
        if (!isset($post->ID)) {
            return false;
        }

        if ($post->post_type != 'product'
            || !$post->ID
            || !is_product()
            || $this->gateway->get_option('widget_enabled') != 'yes'
        ) {
            return false;
        }
        return true;
    }
         
	public function add_meta_tags( $array ) { 
        global $post;
		$product = new WC_Product( $post->ID );
		if ($product->get_id()) {
			echo '<meta name="easycredit-widget-selector" content="'.$this->gateway->get_option('widget_selector').'">';
			echo '<meta name="easycredit-widget-price" content="'.$product->get_price().'">';
			echo '<meta name="easycredit-api-key" content="'.$this->gateway->get_option('api_key').'">';
		}
	}
}
