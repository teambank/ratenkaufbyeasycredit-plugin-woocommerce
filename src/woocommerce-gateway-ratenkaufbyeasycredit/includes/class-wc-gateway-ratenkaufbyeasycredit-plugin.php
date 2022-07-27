<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Teambank\RatenkaufByEasyCreditApiV3 as ApiV3;

class WC_Gateway_Ratenkaufbyeasycredit_Plugin {

    public $id;
    public $file;
    public $plugin_path;
    public $plugin_url;
    public $includes_path;
    public $gateway;

    public function __construct($file) {

        $this->id              = WC_RATENKAUFBYEASYCREDIT_ID;
        $this->file             = $file;
        
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

        add_action('admin_enqueue_scripts', array($this, 'enqueue_backend_resources'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_resources'));
        add_action('do_meta_boxes', array($this, 'hook_prevent_shipping_address_change'));
        
        add_action('admin_post_wc_ratenkaufbyeasycredit_verify_credentials', array($this, 'verify_credentials'));
        add_filter('plugin_action_links_' . plugin_basename( $this->file ), array($this,'plugin_links') );

        add_shortcode($this->get_review_shortcode(), array($this->get_gateway(), 'payment_review'));

        add_action( 'init', function() {
            add_rewrite_tag( '%easycredit%', '([^/]+)' );
            add_rewrite_rule( 'easycredit/(authorize)/secToken/([^/]+)/?', 'index.php?easycredit[action]=$matches[1]&easycredit[sec_token]=$matches[2]', 'top' );
        });
        add_action ('template_redirect', array($this, 'handle_controller'));
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
        flush_rewrite_rules();

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

    public function handle_controller () {
        global $wp_query;

        $params = $wp_query->get( 'easycredit' );
        if ( ! empty( $params['action'] ) ) {
            if (method_exists($this, $params['action'].'Action')) {
                $this->{$params['action'].'Action'}($params);
            }
        }
    }

    public function authorizeAction($params) {
        $txId = $_GET['transactionId'];
        $secToken = $params['sec_token'];

        $args = array(
            'post_type' => 'shop_order',
            'post_status' => 'any',
            'limit' => 1,
            'return' => 'ids',
            'meta_query' => array(
                'relation' => 'AND',
                array(
                    'key' => 'ratenkaufbyeasycredit-transaction-id',
                    'value' => $txId,
                    'compare' => '=',
                ),
                array(
                    'key' => 'ratenkaufbyeasycredit-sec-token',
                    'value' => $params['sec_token'],
                    'compare' => '=',
                )
            )
        );

        $orders = new WP_Query($args);
        $order = wc_get_order(current($orders->posts));

        if ($order) {
            $order->payment_complete(
                $txId
            );
            http_response_code(200);
            exit;
        }
        http_response_code(404);
        exit;
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
             ),
             'woocommerce_easycredit_infopage_page_id' => array(
                'name'    => _x( 'easycredit-infopage', 'Page slug', 'woocommerce' ),
                'title'   => _x( 'ratenkauf by easyCredit - Einfach. Fair. In Raten zahlen', 'Page title', 'woocommerce' ),
                'content' => '<easycredit-infopage></easycredit-infopage>',
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
        load_template( $template, false );
    }

    public function add_module_nomodule_attribute($tag, $handle, $src) {
        if ( 'easycredit-components-module' === $handle ) {
            $src = remove_query_arg( 'ver', $src );
            return '<script type="module" src="' . esc_url( $src ) . '"></script>';
        }
        if ( 'easycredit-components-nomodule' === $handle ) {
            $src = remove_query_arg( 'ver', $src );
            return '<script nomodule src="' . esc_url( $src ) . '"></script>';
        }
        return $tag;
    }

    public function enqueue_easycredit_components () {
        wp_register_script('easycredit-components-module', 'https://easycredit-ratenkauf-webcomponents.netzkollektiv.com/easycredit-components/easycredit-components.esm.js', [], '1.0');
        wp_enqueue_script('easycredit-components-module');
        wp_register_script('easycredit-components-nomodule', 'https://easycredit-ratenkauf-webcomponents.netzkollektiv.com/easycredit-components/easycredit-components.js', [], '1.0');
        wp_enqueue_script('easycredit-components-nomodule');
        add_filter('script_loader_tag', array($this, 'add_module_nomodule_attribute'), 10, 3);
    }

    public function enqueue_frontend_resources($hook) {
        $this->enqueue_easycredit_components();

        wp_enqueue_script('wc_ratenkaufbyeasycredit_js',
            $this->plugin_url . 'assets/js/easycredit.min.js', ['jquery'], '1.0');
        wp_enqueue_style( 'wc_ratenkaufbyeasycredit_css', 
            $this->plugin_url. 'assets/css/easycredit.min.css');
    }

    public function enqueue_backend_resources($hook) {
        $this->enqueue_easycredit_components();

        wp_enqueue_script('wc_ratenkaufbyeasycredit_js', 
            $this->plugin_url . 'assets/js/easycredit-backend.js', ['jquery'], '1.0');

        wp_localize_script( 'wc_ratenkaufbyeasycredit_js', 'wc_ratenkaufbyeasycredit_config', array('url' => admin_url( 'admin-post.php' )) );

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
        $html = preg_replace('/(<h3>.+?)(<a .+?class="edit_address">.+?<\/a>)(.+?load_customer_shipping.+?<\/h3>)/sU','$1$3'.$note, (string)$html);
        ob_end_clean();
        echo $html;
    }
    
    public function verify_credentials() {

        $status = array(
            'status' => true,
            'msg' => __('Credentials valid!','woocommerce-gateway-ratenkaufbyeasycredit')
        );

        $payment = new WC_Gateway_RatenkaufByEasyCredit();
        if ($error =  $payment->check_credentials($_REQUEST['api_key'], $_REQUEST['api_token'], $_REQUEST['api_signature'])) {
            $status = array(
                'status' => false,
                'msg' => $error
            );
        }

        wp_send_json($status);
    }

    public function plugin_links( $links ) {
        $plugin_links = array(
            '<a href="' . admin_url( 'admin.php?page=wc-settings&tab=checkout&section=ratenkaufbyeasycredit' ) . '">' . __( 'Settings', 'wc-gateway-ratenkaufbyeasycredit' ) . '</a>'
        );
        return array_merge( $plugin_links, $links );
    }
}
