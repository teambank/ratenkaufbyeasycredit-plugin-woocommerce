<?php
class WC_Gateway_Ratenkaufbyeasycredit_Widget {

    public function __construct($plugin) {
    	$this->plugin = $plugin;
    	$this->plugin_url = $plugin->plugin_url;
    	$this->gateway = $this->plugin->get_gateway();

    	add_action ( 'wp', array($this, 'run'));
    }

    public function run() {

    	global $post;
        if (!isset($post->ID)) {
            return;
        }

    	$this->product = $post->ID;
    	
    	if ($post->post_type != 'product'
    		|| !$this->product
    		|| !is_product()
    		|| $this->gateway->get_option('widget_enabled') != 'yes'
    	) {
	    	return;
	    }
	    
   		add_action ( 'wp_head', array($this, 'add_meta_tags') );
        add_action ( 'wp_enqueue_scripts', array($this, 'enqueue_frontend_ressources'));
    }
         
	public function add_meta_tags( $array ) { 

		$product = new WC_Product( $this->product );
		if ($product->get_id()) {
			echo '<meta name="easycredit-product-price" content="'.$product->get_price().'">';
			echo '<meta name="easycredit-api-key" content="'.$this->gateway->get_option('api_key').'">';
		}
	}
	
    public function enqueue_frontend_ressources($hook) {
	    wp_enqueue_script('wc_ratenkaufbyeasycredit_frontend_js', 
	    	$this->plugin_url . 'assets/js/easycredit-frontend.js', 'wc_ratenkaufbyeasycredit_widget_js', '1.0');
	    wp_enqueue_script('wc_ratenkaufbyeasycredit_widget_js', 
	    	$this->plugin_url . 'assets/js/easycredit-widget.js', 'jquery', '1.0');
	    wp_enqueue_style( 'wc_ratenkaufbyeasycredit_widget_css', 
	    	$this->plugin_url. 'assets/css/easycredit-widget.css' );
	}
}
