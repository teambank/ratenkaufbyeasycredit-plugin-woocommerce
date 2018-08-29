<?php
abstract class WC_Gateway_Ratenkaufbyeasycredit_Widget {

    public function __construct($plugin) {
    	$this->plugin = $plugin;
    	$this->plugin_url = $plugin->plugin_url;
    	$this->gateway = $this->plugin->get_gateway();

    	add_action ( 'wp', array($this, 'run'));
    }

    public function run() {

        if (!$this->should_be_displayed()) {
            return;
        }
	    
   		add_action ( 'wp_head', array($this, 'add_meta_tags') );
        add_action ( 'wp_enqueue_scripts', array($this, 'enqueue_frontend_ressources'));
    }

    abstract protected function should_be_displayed();
    abstract public function add_meta_tags($array);
     
    public function enqueue_frontend_ressources($hook) {
        wp_enqueue_script('wc_ratenkaufbyeasycredit_js',
            $this->plugin_url . 'assets/js/easycredit.min.js', 'wc_ratenkaufbyeasycredit_widget_js', '1.0');
        wp_enqueue_style( 'wc_ratenkaufbyeasycredit_css',
            $this->plugin_url. 'assets/css/easycredit.min.css' );
	}
}
