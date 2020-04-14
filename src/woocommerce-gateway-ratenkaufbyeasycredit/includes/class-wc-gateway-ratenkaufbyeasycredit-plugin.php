<?php
class WC_Gateway_Ratenkaufbyeasycredit_Plugin {

    public function __construct($file, $version) {

	    $this->id 			 = WC_RATENKAUFBYEASYCREDIT_ID;
	    $this->file			 = $file;
	    
        $this->plugin_path   = trailingslashit( plugin_dir_path( $this->file ) );
        $this->plugin_url    = trailingslashit( plugin_dir_url( $this->file ) );
        $this->includes_path = $this->plugin_path . trailingslashit( 'includes' );

    }

    public function run() {
    	require_once $this->includes_path.'class-wc-gateway-ratenkaufbyeasycredit-loader.php';
        $loader = new WC_Gateway_Ratenkaufbyeasycredit_Loader($this);

        if (!is_admin()) {
	        new WC_Gateway_Ratenkaufbyeasycredit_Widget_Product($this);
	        new WC_Gateway_Ratenkaufbyeasycredit_Widget_Cart($this);
        }

        if (is_admin()) {
            new WC_Gateway_Ratenkaufbyeasycredit_Order_Management($this);
        }

        add_action( 'rest_api_init', array($this, 'init_api'));

        add_action('admin_enqueue_scripts', array($this, 'enqueue_backend_ressources'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_ressources'));
        add_action('do_meta_boxes', array($this, 'hook_prevent_shipping_address_change'));
        
        add_action('admin_post_wc_ratenkaufbyeasycredit_verify_credentials', array($this, 'verify_credentials'));
        add_filter('plugin_action_links_' . plugin_basename( $this->file ), array($this,'plugin_links') );

        add_shortcode($this->get_review_shortcode(), array($this->get_gateway(), 'payment_review'));

    }

    public function init_api() {
        new WC_Gateway_Ratenkaufbyeasycredit_RestApi(
            $this,
            new WC_Gateway_Ratenkaufbyeasycredit_Order_Management($this)
        );
    }

    public function get_gateway() {
    	if (!isset($this->gateway)) {
	    	$this->gateway = new WC_Gateway_RatenkaufByEasyCredit();
	    }
	    return $this->gateway;
    }

    public function maybe_run() {
        add_action('plugins_loaded', array($this,'run') );
        add_action('init',array($this,'load_textdomain'));

        register_activation_hook( $this->file, array( $this, 'activate' ) );
        register_deactivation_hook( $this->file, array( $this, 'deactivate' ) );
        register_uninstall_hook(__FILE__, 'uninstall');
        add_action('wpmu_new_blog', array($this,'activate_new_blog'), 10, 6 );
    }

    public function activate_new_blog($blog_id, $user_id, $domain, $path, $site_id, $meta) {
        if ( ! function_exists( 'is_plugin_active_for_network' ) ) {
            require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
        }
        if ( is_plugin_active_for_network(plugin_basename($this->file)) ) {
            switch_to_blog($blog_id);
            $this->activate_single_site(); 
            restore_current_blog();
        }
    }

    public function activate($network_wide) {
        if ( is_multisite() && $network_wide ) { 
            global $wpdb;

            foreach ($wpdb->get_col("SELECT blog_id FROM $wpdb->blogs") as $blog_id) {
                switch_to_blog($blog_id);
                $this->activate_single_site();
                restore_current_blog();
            } 

        } else {
            $this->activate_single_site();
        }
    }
 
    public function deactivate() {
	    // nothing to do here currently	    
    }

    public static function uninstall() {
	    // nothing to do here currently
    }

    public function activate_single_site() {
        require_once( WC_ABSPATH . 'includes/admin/wc-admin-functions.php' );

        $pages = $this->get_review_page_data();

        foreach ( $pages as $key => $page ) {
            $id = wc_create_page( 
            	esc_sql( $page['name'] ), 
            	$key, 
            	$page['title'], 
            	$page['content']
            );
        }
        delete_transient( 'woocommerce_cache_excluded_uris' );
    }
    
    public function get_review_page_data() {
	 	return array(
	 		'woocommerce_easycredit_checkout_review_page_id' => array(
                'name'    => _x( 'easycredit-checkout-review', 'Page slug', 'woocommerce' ),
                'title'   => _x( 'Review Order', 'Page title', 'woocommerce' ),
                'content' => '['.$this->get_review_shortcode().']',
            )
        );
    }
    
    public function get_review_page_uri() {
    	$pageId = get_option(
    		key($this->get_review_page_data())
    	);
	    return get_permalink($pageId);
    }

    public function get_review_shortcode() {
	    return 'woocommerce_'.$this->id.'_checkout_review';
    }

    public function load_textdomain() {
    	load_plugin_textdomain( 'woocommerce-gateway-ratenkaufbyeasycredit', 
    		false, 
    		basename( dirname( $this->file ) ) . '/languages/'
        );
    }

    public function load_template($tpl, $data = array()) {
        foreach ($data as $k => $v) {
            set_query_var( $k, $v );
        }

        $template = $this->plugin_path . '/templates/'.$tpl.'.php';
         if ( $_template = locate_template( $tpl.'.php' ) ) {
            $template = $_template;
        }
        load_template( $template );
    }

    public function enqueue_frontend_ressources($hook) {
        wp_enqueue_script('wc_ratenkaufbyeasycredit_js',
            $this->plugin_url . 'assets/js/easycredit.min.js', 'wc_ratenkaufbyeasycredit_widget_js', '1.0');
	    wp_enqueue_style( 'wc_ratenkaufbyeasycredit_css', 
	    	$this->plugin_url. 'assets/css/easycredit.min.css', 'wc_ratenkaufbyeasycredit_css' );
	}

    public function enqueue_backend_ressources($hook) {
	    if ('woocommerce_page_wc-settings' !== $hook) {
	        return;
	    }
	    wp_enqueue_script('wc_ratenkaufbyeasycredit_js', 
	    	$this->plugin_url . 'assets/js/easycredit-backend.js', 'jquery', '1.0');
	    wp_enqueue_style( 'wc_ratenkaufbyeasycredit_css', 
	    	$this->plugin_url. 'assets/css/easycredit-backend.css' );
	}

    public function hook_prevent_shipping_address_change($box) {
        global $wp_meta_boxes;
        $wp_meta_boxes['shop_order']['normal']['high']['woocommerce-order-data']['callback'] = get_class($this).'::prevent_shipping_address_change';
    }

    public static function prevent_shipping_address_change($post) {
        global $theorder;

        if ( ! is_object( $theorder ) ) {
            $theorder = wc_get_order( $post->ID );
        }

        $order = $theorder;
        if ($order->get_payment_method() != 'ratenkaufbyeasycredit') {
            WC_Meta_Box_Order_Data::output($post);
            return;
        }

        $note = '<p>Die Versandadresse kann bei ratenkauf by easyCredit nicht nachträglich verändert werden.</p>';

        ob_start();
        WC_Meta_Box_Order_Data::output($post);
        $html = ob_get_contents();
        $html = preg_replace('/(<h3>.+?)(<a .+?class="edit_address">.+?<\/a>)(.+?load_customer_shipping.+?<\/h3>)/sU','$1$3'.$note,$html);
        ob_end_clean();
        echo $html;
    }
	
	public function verify_credentials() {
	    $payment = new WC_Gateway_RatenkaufByEasyCredit();
	
	    $status = array(
	        'status' => false,
	        'msg' => __('Credentials invalid. Please check your input!','woocommerce-gateway-ratenkaufbyeasycredit')
	    );
	
	    if ($payment->get_checkout()->verifyCredentials($_REQUEST['api_key'], $_REQUEST['api_token'])) {
	        $status = array(
	            'status' => true,
	            'msg' => __('Credentials valid!','woocommerce-gateway-ratenkaufbyeasycredit')
	        );
	    }
	    wp_send_json($status);
	    exit;
	}

	public function plugin_links( $links ) {
	    $plugin_links = array(
	        '<a href="' . admin_url( 'admin.php?page=wc-settings&tab=checkout&section=ratenkaufbyeasycredit' ) . '">' . __( 'Settings', 'wc-gateway-ratenkaufbyeasycredit' ) . '</a>'
	    );
	    return array_merge( $plugin_links, $links );
	}
}
