<?php
class WC_Gateway_RatenkaufByEasyCredit extends WC_Payment_Gateway {

    public static $initialized = false;

    public $_storage = null;

    public function __construct() {

        $this->plugin               = wc_ratenkaufbyeasycredit();
        
        $this->id                 = WC_RATENKAUFBYEASYCREDIT_ID;
        $this->icon               = apply_filters(
            'woocommerce_ratenkaufbyeasycredit_icon', 
            'https://www.easycredit-ratenkauf.de/download/200x43_Ratenkauf_Logo_mitSubline.png'
        );
        
        $this->has_fields         = false;
        $this->method_title       = __( 'ratenkauf by easyCredit', 'woocommerce-gateway-ratenkaufbyeasycredit');
        $this->method_description = __( 'ratenkauf by easyCredit - jetzt die einfachste Teilzahlungslösung Deutschlands nutzen. Unser Credo einfach, fair und sicher gilt sowohl für Ratenkaufkunden als auch für Händler. Der schnelle, einfache und medienbruchfreie Prozess mit sofortiger Online-Bonitätsprüfung lässt sich sicher in den Onlineshop integrieren. Wir übernehmen das Ausfallrisiko und Sie können Ihren Umsatz bereits nach drei Tagen verbuchen.' );

        $this->init_form_fields();
        $this->init_settings();

        $title = $this->get_option( 'title' );
        $this->title        = !empty($title) ? $title : $this->method_title;
        $this->description  = '';
        $this->instructions = $this->get_option( 'instructions' );
        $this->debug        = $this->get_option( 'debug', false );

        $this->has_fields = true;

        $this->order_button_text  = __('Continue to pay by installments', 'woocommerce-gateway-ratenkaufbyeasycredit');

        if (self::$initialized) {
            return; // initialize payment gateway only once, e.g. WPML Woocommerce tries to initialize again which results in duplicate/wrong behavior
        }

        if (!is_admin()) {
            add_action( 'wp', array($this, 'maybe_expire_order') );        
            add_action( 'wp', array($this, 'maybe_return_from_payment_page') );
            add_action( 'wp', array($this, 'maybe_order_confirm') );

            add_action ('woocommerce_checkout_create_order', 
                array($this, 'proccess_payment_order_details')
            );
            add_action('woocommerce_before_pay_action',
                array($this, 'proccess_payment_order_details')
            );
            add_action( 'woocommerce_ratenkaufbyeasycredit_order_item_totals', 
                array( $this, 'order_item_totals' )
            );

        }

        if (is_admin()) {
            add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, 
                array( $this, 'process_admin_options' ) 
            );
            add_action( 'admin_notices', array($this, 'check_credentials') );
            add_action( 'admin_notices', array($this, 'check_review_page_exists') );
        }
        
        add_action( 'woocommerce_email_before_order_table', array( $this, 'email_instructions' ), 10, 3 );

        self::$initialized = true;
    }

    public function admin_options() {
        ob_start();
        parent::admin_options();
        $parent_options = ob_get_contents();
        ob_end_clean();

        $shipping_methods = '';
        if (WC()->shipping()) {
            foreach (WC()->shipping()->load_shipping_methods() as $code => $method) {
                $shipping_methods .= '<option value="'.$method->id.'">'.$method->get_method_title().'</option>';
            }
        }
        $parent_options = preg_replace(
            '!(id="woocommerce_ratenkaufbyeasycredit_clickandcollect_shipping_method".*?>)(.+?)(</select>)!s',
            '$1$2'.$shipping_methods.'$3',
            $parent_options
        );


        ?>
        <div class="ratenkaufbyeasycredit-wrapper">
            <div class="easycredit-intro">
              <img src="https://www.easycredit-ratenkauf.de/download/200x43_Ratenkauf_Logo_mitSubline.png">
              <div>
                Bieten Sie Ihren Kunden die Möglichkeit der Ratenzahlung mit ratenkauf by easyCredit.<br>
                <strong>Einfach. Fair. In Raten zahlen.</strong>
                <br><br>
                <a href="https://partner.easycredit-ratenkauf.de/portal/" target="_blank">zum Partnerportal</a>
                 - <a href="https://www.easycredit-ratenkauf.de/shopsysteme.htm" target="_blank">zum Integration-Center</a>
                 - <a href="https://netzkollektiv.com/docs/ratenkaufbyeasycredit-woocommerce/" target="_blank">zur Dokumentation</a> 
              </div>
            </div>
            <!-- style>
            .easycredit-intro {
              padding: 15px 0;
              background:#fff;
            }
            .easycredit-intro img {
              display:inline-block; 
              padding:15px; 
              width:170px;
            }
            .easycredit-intro div {
              display:inline-block;
            }
            </style -->

          <?php echo $parent_options; ?>
        </div>
        <?php
    }

    public function validate_fields() {

        global $wp;
        if (isset($wp->query_vars['order-pay'])) {
            $order = wc_get_order($wp->query_vars['order-pay']);
        } else {
            $order = $this->get_tmp_order();
        }

        try {
            $quote = new \Netzkollektiv\EasyCredit\Api\Quote($order, $this);
            $checkout = $this->get_checkout();
            $checkout->isAvailable($quote);
        } catch(\Exception $e) {
            $error = $e->getMessage();
            wc_add_notice( sprintf(__(
                '%s: '.$error,
                'woocommerce-gateway-ratenkaufbyeasycredit'
            ),$this->get_title()),
            'error' );

            return;
        }

        if ( ! $_POST['ratenkaufbyeasycredit-agreement'] ) {
            wc_add_notice( sprintf(__( 
                '%s: Please agree to the privacy conditions.', 
                'woocommerce-gateway-ratenkaufbyeasycredit'
            ),$this->get_title()), 
            'error' );
        }
        
        if ( ! $_POST['ratenkaufbyeasycredit-prefix'] 
            || !$this->get_checkout()->isPrefixValid($_POST['ratenkaufbyeasycredit-prefix']) 
        ) {
            wc_add_notice( sprintf(__( 
                '%s: Please select a title.',
                'woocommerce-gateway-ratenkaufbyeasycredit'
            ), $this->get_title())
            , 'error' );
        }
    
    }
    
    public function get_title() {
        $backtrace = debug_backtrace();
        if ($backtrace[1]['function'] == 'include') {
            $this->plugin->load_template('payment-method-title',array(
                'title' => parent::get_title()
            ));
            return;
        }
        return parent::get_title();
    }

    public function get_icon() {
        $backtrace = debug_backtrace();
        if ($backtrace[1]['function'] == 'include') {
            return '';
        }
        return parent::get_icon();
    }

    protected function get_current_order() {
        $order_id = $this->get_storage()->get('order_id');
        if (!$order_id) {
            return false;
        }
           
        return wc_get_order($order_id);
    }

    public function payment_review() {
        if (is_admin()) {
            return;
        }
        if (!$order = $this->get_current_order()) {
            return;
        }    
            
        $this->get_checkout()->loadFinancingInformation();        

        ob_start();
        $this->plugin->load_template('review-order', array(
            'gateway' => $this,
            'order'      => $order
        ));
        $content = ob_get_contents();
        ob_end_clean();
        return $content;
    }
    
    public function maybe_expire_order() {
        if (!$order = $this->get_current_order()) {
            return;
        }
        
        $quote = new \Netzkollektiv\EasyCredit\Api\Quote($order, $this);

        $checkout = $this->get_checkout();        
        if ($this->get_storage()->get('authorized_amount') != $quote->getGrandTotal()
            && !$checkout->verifyAddressNotChanged($quote)
        ) {
            $checkout->clear();
        }    
    }
    
    public function maybe_return_from_payment_page() {
        if (!isset($_GET['woo-'.$this->id.'-return'])) {
            return;
        }

        try {
            $checkout = $this->get_checkout();
            
            if (!$checkout->isInitialized()
                || !$checkout->isApproved()
            ) {
                throw new \Exception(__('Transaction not approved', 'woocommerce-gateway-ratenkaufbyeasycredit'));
            }

        } catch (\Exception $e) {
               $this->_handleError($e->getMessage());
        }
    }
    
    public function maybe_order_confirm() {
        if (!isset($_POST['woo-'.$this->id.'-confirm'])) {
            return;
        }
       
        if (!$order = $this->get_current_order()) {
            $this->_handleError('Could not find order');
            return;
        }

        if (!wp_verify_nonce($_POST['_wpnonce'], 'woocommerce-easycredit-pay')) {
            $this->_handleError('Could not verify nonce');
        }
        
          try {
            
            $checkout = $this->get_checkout();
    
            if (!$checkout->isInitialized()
                || !$checkout->isApproved()
            ) {
                throw new \Exception(__('Transaction not approved', 'woocommerce-gateway-ratenkaufbyeasycredit'));
            }

            ob_start(); // Suppress error output from akismet
    
            $checkout->capture(null, $order->get_order_number());

            $transaction_id = $this->get_storage()->get('transaction_id');
               
            $order->payment_complete(
                $transaction_id
            );
            $order->add_meta_data($this->id.'-interest-amount',$this->get_storage()->get('interest_amount'),true);
            $order->add_meta_data($this->id.'-transaction-id',$transaction_id,true);
            $order->save();
            
            WC()->cart->empty_cart();        
            $checkout->clear();
            
            ob_end_clean();
            
               wp_redirect( $order->get_checkout_order_received_url() );
               exit;
        } catch (\Exception $e) {
            $this->_handleError($e->getMessage());
        }
    }
    
    protected function _handleError($message) {
        error_log($message);
        wc_add_notice( __($message, 'woocommerce-gateway-ratenkaufbyeasycredit'), 'error' );
        $this->get_checkout()->clear();

        $url = wc_get_page_permalink( 'cart' );
        if ($order = $this->get_current_order()) {
            $url = $order->get_cancel_order_url_raw();
        }
        wp_safe_redirect( $url );
        exit;        
    }
    
    public function check_credentials() {
        if (get_current_screen()->parent_base !== 'woocommerce' ||
            get_transient( $this->id.'-settings-checked' )
        ) {
            return;
        }

        $settingsUri = admin_url( 'admin.php?page=wc-settings&tab=checkout&section=ratenkaufbyeasycredit' );

        $apiKey = $this->get_option('api_key');
        $apiToken = $this->get_option('api_token');

        if (!empty($apiKey) && !empty($apiToken)) {
            try {
                if (!$this->get_checkout()->verifyCredentials($apiKey, $apiToken)) {
                    echo $this->_display_settings_error(array(
                        __('ratenkauf by easyCredit credentials are not valid.','woocommerce-gateway-ratenkaufbyeasycredit'),
                        __('Please go to <a href="%s">plugin settings</a> and correct API Key and API Token.','woocommerce-gateway-ratenkaufbyeasycredit')
                    ));
                    return;
                }
                set_transient( $this->id.'-settings-checked', true, DAY_IN_SECONDS );
            } catch (\Exception $e) {
                error_log($e->getMessage());
            }
        } else {
            echo $this->_display_settings_error(
                __('Please enter your credentials to use ratenkauf by easyCredit payment plugin in the <a href="%s">plugin settings</a>.','woocommerce-gateway-ratenkaufbyeasycredit')
            );
            return;
        }
    }

    public function check_review_page_exists() {
        if (get_current_screen()->parent_base !== 'woocommerce') {
            return;
        }

        $page_path = current($this->plugin->get_review_page_data())['name'];
        if (get_page_by_path( $page_path, OBJECT )) {
            return;
        }

        echo $this->_display_settings_error(
            __('The "ratenkauf by easyCredit" review page does not exist. Probably it was deleted by mistake. The page is necessary to confirm "ratenkauf by easyCredit" payments after being returned from the payment terminal. To restore the page, please restore it from the trash under "Pages", or deactivate and activate the plugin in the <a href="%s">plugin administration</a>.','woocommerce-gateway-ratenkaufbyeasycredit'),
            is_multisite() ? admin_url('network/plugins.php?s=easycredit') :  admin_url('plugins.php?s=easycredit')
        );
        return;
    }

    public function abort_create_order($order) {
        $this->tmp_order = $order;
        throw new Exception(__CLASS__.'_tmp_order');
    }

    public function prevent_remove_items() {
        return false;
    }

    public function get_tmp_order() {

        add_action ('woocommerce_checkout_create_order', array($this, 'abort_create_order'));
        add_filter ('woocommerce_order_has_status', array($this, 'prevent_remove_items'));

        $wc_checkout = WC_Checkout::instance();
        $postData = array();
        if (isset($_POST['post_data'])) {
            parse_str($_POST['post_data'],$postData);
        } else {
            $postData = $_POST;
        }
        $postData['payment_method'] = 'easycredit';

        $wc_checkout->create_order($postData);

        remove_filter ('woocommerce_order_has_status', array($this, 'prevent_remove_items'));
        remove_action('woocommerce_checkout_create_order', array($this, 'abort_create_order'), 10 );

        $order = $this->tmp_order;
        if ($order && isset($postData['ship_to_different_address'])) {
            $order->add_meta_data('ship_to_different_address',$postData['ship_to_different_address']);
        }
        return $order;
    }

    public function payment_fields() {
        $error = false;
        $checkout = $this->get_checkout();

        global $wp;
        if (isset($wp->query_vars['order-pay'])) {
            $order = wc_get_order($wp->query_vars['order-pay']);
        } else {
            $order = $this->get_tmp_order();
        }

        if (is_null($order)) {
            return;
        }

        try {
            $quote = new \Netzkollektiv\EasyCredit\Api\Quote($order, $this);
            $checkout->isAvailable($quote);
        } catch(\Exception $e) {
            $error = $e->getMessage();
        }

        $agreement = '';
        if (!$error) {
            try {
                $transientKey = $this->id.'-agreement';
                if (false === ( $agreement = get_transient( $transientKey ) )) {
                    $agreement = $checkout->getAgreement();
                    set_transient($transientKey, $agreement, 24 * HOUR_IN_SECONDS );                    
                }
            } catch (\Exception $e) { }
        }

        if ($error && trim($error) == 'Der Webshop existiert nicht.') {
            $error = 'ratenkauf by easyCredit zur Zeit nicht verfügbar.';
        }

        if ($quote->getBillingAddress()->getCountryId() != 'DE' &&
            $quote->getBillingAddress()->getCountryId() != '' 
        ) {
            $error = 'ratenkauf by easyCredit ist leider nur in Deutschland verfügbar.';
        }

        $this->plugin->load_template('payment-fields', array(
            'easyCredit'    => $this,
            'easyCreditError' => $error,
            'easyCreditAgreement' => $agreement
        ));
    }

    protected function _display_settings_error($msg, $uri = null) {
        if (is_array($msg)) {
            $msg = implode(' ',$msg);
        }

        if ($uri === null) {
            $uri = admin_url( 'admin.php?page=wc-settings&tab=checkout&section=ratenkaufbyeasycredit' );
        }
        return implode(array(
            '<div class="error"><p>',
                sprintf( $msg, $uri),
            '</p></div>'
        ));
    }

    public function init_form_fields() {
        $fields = require (wc_ratenkaufbyeasycredit()->includes_path.'admin-fields.php');
        $fields = apply_filters( 'wc_ratenkaufbyeasycredit_form_fields', $fields);
        $this->form_fields = $fields;
    }

    public function generate_clickandcollectintro_html() {
        return file_get_contents(dirname(__FILE__).'/../templates/click-and-collect.html');
    }

    public function get_option($key, $empty_value = null) {
        $option = parent::get_option($key,$empty_value);
        if ($key == 'api_verify_credentials') {
            // always return default value for button
            return $this->get_field_default(
                $this->get_form_fields()[$key]
            );
        }
        return $option;
    }

    public function thankyou_page() {
        if ( $this->instructions ) {
            echo wpautop( wptexturize( $this->instructions ) );
        }
    }

    public function email_instructions( $order, $sent_to_admin, $plain_text = false ) {

        if ( $this->instructions && 
            ! $sent_to_admin && 
            $this->id === $order->payment_method
        ) {
            echo wpautop( wptexturize( $this->instructions ) ) . PHP_EOL;
        }
    }
    
    public function get_storage() {
        if ($this->_storage == null) {
            $this->_storage = new \Netzkollektiv\EasyCredit\Api\Storage();
        }
        return $this->_storage;
    }

    public function get_checkout() {

        $logger = new \Netzkollektiv\EasyCredit\Api\Logger($this);
        $config = new \Netzkollektiv\EasyCredit\Api\Config($this);
        $clientFactory = new \Netzkollektiv\EasyCreditApi\Client\HttpClientFactory();

        $client = new \Netzkollektiv\EasyCreditApi\Client(
            $config,
            $clientFactory,
            $logger
        );
        $storage = $this->get_storage();
        
        return new \Netzkollektiv\EasyCreditApi\Checkout(
            $client,
            $storage
        );
    }

     public function get_merchant_client() {
        $logger = new \Netzkollektiv\EasyCredit\Api\Logger($this);
        $config = new \Netzkollektiv\EasyCredit\Api\Config($this);
        $clientFactory = new \Netzkollektiv\EasyCreditApi\Client\HttpClientFactory();

        return new \Netzkollektiv\EasyCreditApi\Merchant(
            $config,
            $clientFactory,
            $logger
        );
    }

    public function get_confirm_url() {
        $query_args = array(
            'woo-'.$this->id.'-return' => true,
        );
        return add_query_arg( $query_args, $this->plugin->get_review_page_uri() );
    }
    
    public function process_payment( $order_id ) {

        $order = wc_get_order( $order_id );

        $checkout = $this->get_checkout();
        $checkout->start(
            new \Netzkollektiv\EasyCredit\Api\Quote($order, $this),
            esc_url_raw( $order->get_cancel_order_url_raw() ),
            $this->get_confirm_url($order_id),
            esc_url_raw( $order->get_cancel_order_url_raw() )
        );

        $storage = new \Netzkollektiv\EasyCredit\Api\Storage();
        $storage->set('order_id',$order_id);
        $storage->set('return_url',$this->get_return_url($order));

        $paymentPageUrl = $checkout->getRedirectUrl();

        if (!$paymentPageUrl) {
            throw new Exception(__(
                'Payment Page URI could not be retrieved',
                'woocommerce-gateway-ratenkaufbyeasycredit'
            ));
        }

        return array(
            'result'     => 'success',
            'redirect'    => $paymentPageUrl
        );
    }

    protected function get_total_including_interest($order) {
        $interest = $this->get_storage()->get('interest_amount');

        $total = $order->get_total();
        $order->set_total($total + $interest);
        $_total = $order->get_formatted_order_total();
        $order->set_total($total);
        
        return $_total;
    }
    
    public function order_item_totals($order) {
        $interest = $this->get_storage()->get('interest_amount');
    
        $_totals = array();
        foreach ($order->get_order_item_totals() as $key => $total) {

            if ($key == 'payment_method') {
                continue;
            }
            if ($key == 'order_total') {
                $_totals['interest'] = array(
                    'label' => __('Interest:', 'woocommerce-gateway-ratenkaufbyeasycredit'),
                    'value' => wc_price($interest, array('currency',$order->get_currency()))
                );
                $total['value'] = $this->get_total_including_interest($order);
            }
            $_totals[$key] = $total;
        }
        return $_totals;
    }
    
    public function capture_payment() {
    
        $order = wc_get_order( $order_id );
        
        $this->get_return_url( $order );        
    }

    public function proccess_payment_order_details($order) {
        foreach (array('prefix') as $attr) {
            $key = $this->id.'-'.$attr;
            if (isset($_POST[$key])) {
                $order->add_meta_data($key,$_POST[$key],true);
            }
        }
    }
} 
