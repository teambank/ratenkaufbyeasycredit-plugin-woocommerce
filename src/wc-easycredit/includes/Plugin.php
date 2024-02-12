<?php
/*
 * (c) NETZKOLLEKTIV GmbH <kontakt@netzkollektiv.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Netzkollektiv\EasyCredit;

use Teambank\RatenkaufByEasyCreditApiV3 as ApiV3;

class Plugin
{
    const META_KEY_TRANSACTION_ID = 'easycredit-transaction-id';
    const META_KEY_SEC_TOKEN = 'easycredit-sec-token';
    const META_KEY_INTEREST_AMOUNT = 'easycredit-interest-amount';
    const META_KEY_TOKEN = 'easycredit-token';

    public $id;

    private $file;

    private $plugin_path;

    public $plugin_url;

    private $rewrite_rules = [
        'easycredit/(cancel)/?' => 'index.php?easycredit[action]=$matches[1]',
        'easycredit/(express)/?' => 'index.php?easycredit[action]=$matches[1]',
        'easycredit/(authorize)/secToken/([^/]+)/?' => 'index.php?easycredit[action]=$matches[1]&easycredit[sec_token]=$matches[2]',
    ];

    private $integration;

    private $express_checkout;

    private $paymentGateways;

    private $reviewPage;

    private $infoPage;

    private $temporaryOrderHelper;

    public function __construct($file)
    {
        $this->id = WC_EASYCREDIT_ID;
        $this->file = $file;
        
        $this->plugin_path = trailingslashit(plugin_dir_path($this->file));
        $this->plugin_url = trailingslashit(plugin_dir_url($this->file));
    }

    public function run()
    {
        $plugin = $this;

        $this->integration = $integration = new Integration(
            $plugin
        );
        $fieldProvider = new Config\FieldProvider();

        $this->temporaryOrderHelper = new Helper\TemporaryOrder();

        $this->paymentGateways = [];
        foreach (['Ratenkauf','Rechnung'] as $method) {
            $class = 'Netzkollektiv\\EasyCredit\\Gateway\\'.$method;
            $this->paymentGateways[$method] = new $class(
                $plugin,
                $integration,
                $fieldProvider,
                $this->temporaryOrderHelper
            );
        }
        $configGeneralSection = new Config\General(
            $fieldProvider
        );
        $sectionsRenderer = new Config\SectionsRenderer(
            $configGeneralSection,
            $this->paymentGateways
        );

        new Admin\RestApi(
            $plugin,
            $integration
        );

        if (!is_admin()) {
            new Widget\Product(
                $plugin,
                $this->paymentGateways['Ratenkauf']
            );
            new Widget\Cart(
                $plugin,
                $this->paymentGateways['Ratenkauf']
            );
            $this->express_checkout = new ExpressCheckout(
                $plugin,
                $integration,
                $this->paymentGateways['Ratenkauf']
            );
            new Marketing\Components(
                $plugin,
                $this->paymentGateways['Ratenkauf']
            );

            $this->reviewPage = new Pages\ReviewPage(
                $plugin,
                $integration,
                $this->express_checkout
            );
            $this->infoPage = new Pages\InfoPage();
        }

        if (is_admin()) {
            new Admin\OrderManagement(
                $plugin,
                $integration
            );
            new Marketing\Blocks($plugin);
        }

        add_filter('woocommerce_payment_gateways', [$this, 'payment_gateways']);

        add_action('admin_enqueue_scripts', [$this, 'enqueue_backend_resources']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_frontend_resources']);
        add_action('do_meta_boxes', [$this, 'hook_prevent_shipping_address_change']);
        
        add_action('admin_post_wc_easycredit_verify_credentials', [$this, 'verify_credentials']);
        add_filter('plugin_action_links_' . plugin_basename($this->file), [$this, 'plugin_links']);


        add_action('init', [$this, 'add_rewrite_rules']);
        add_action('admin_init', [$this, 'check_rewrite_rules']);
        add_action('template_redirect', [$this, 'handle_controller']);
    }

    public function maybe_run()
    {
        add_action('plugins_loaded', [$this, 'run']);
        add_action('init', [$this, 'load_textdomain']);

        add_action('admin_init', [$this, 'apply_migrations']);

        register_activation_hook($this->file, [$this, 'activate']);
        register_deactivation_hook($this->file, [$this, 'deactivate']);
        register_uninstall_hook(__FILE__, 'uninstall');
        add_action('wpmu_new_blog', [$this, 'activate_new_blog'], 10, 6);
    }

    public function activate_new_blog($blog_id, $user_id, $domain, $path, $site_id, $meta)
    {
        if (!function_exists('is_plugin_active_for_network')) {
            require_once(\ABSPATH . '/wp-admin/includes/plugin.php');
        }
        if (is_plugin_active_for_network(plugin_basename($this->file))) {
            switch_to_blog($blog_id);
            $this->activate_single_site();
            restore_current_blog();
        }
    }

    public function activate($network_wide)
    {
        $this->add_rewrite_rules();
        flush_rewrite_rules();

        if (is_multisite() && $network_wide) {
            global $wpdb;

            foreach ($wpdb->get_col("SELECT blog_id FROM {$wpdb->blogs}") as $blog_id) {
                switch_to_blog($blog_id);
                $this->activate_single_site();
                restore_current_blog();
            }
        } else {
            $this->activate_single_site();
        }
    }
 
    public function deactivate()
    {
        // nothing to do here currently
    }

    public static function uninstall()
    {
        // nothing to do here currently
    }

    public function add_rewrite_rules()
    {
        add_rewrite_tag('%easycredit%', '([^/]+)');
        foreach ($this->rewrite_rules as $regex => $query) {
            add_rewrite_rule($regex, $query, 'top');
        }
    }

    public function check_rewrite_rules()
    {
        $flush = false;
        $rules = get_option('rewrite_rules');
        foreach ($this->rewrite_rules as $regex => $query) {
            if (!isset($rules[$regex])) {
                $flush = true;
            }
        }

        if ($flush) {
            flush_rewrite_rules();
        }
    }

    public function get_method_by_payment_type($paymentType) {
        return current(array_filter($this->paymentGateways, function ($gateway) use ($paymentType) {
            return $paymentType === $gateway::PAYMENT_TYPE;
        }));        
    }

    public function payment_gateways($gateways)
    {
        foreach ($this->paymentGateways as $payment_gateway) {
            $gateways[] = $payment_gateway;
        }
        return $gateways;
    }

    public function get_option($option_key, $default_value = false) {
        $options = get_option('woocommerce_easycredit_settings', $default_value);
        if (isset($options[$option_key])) {
            return $options[$option_key];
        }
        return null;
    }

    public function handle_controller()
    {
        global $wp_query;

        $params = $wp_query->get('easycredit');
        if (!empty($params['action'])) {
            if (method_exists($this, $params['action'] . 'Action')) {
                $this->{$params['action'] . 'Action'}($params);
            }
        }
    }

    public function get_transient($name)
    {
        return ($this->get_option('debug')) ? false : get_transient($name);
    }

    public function expressAction()
    {
        try {
            try {
                $this->integration->storage()
                    ->set('express', true);

                $quote = $this->integration->quote_builder()->build(
                    $this->temporaryOrderHelper->get_order()
                );

                $checkout = $this->integration->checkout();
                $checkout->start($quote);

                wp_redirect($checkout->getRedirectUrl());
                exit;
            } catch (ApiV3\ApiException $e) {
                if ($e->getResponseObject() instanceof ApiV3\Model\ConstraintViolation) {
                    $error = 'easyCredit: ';
                    foreach ($e->getResponseObject()->getViolations() as $violation) {
                        $error .= $violation->getMessage();
                    }
                    throw new \Exception($error);
                }
                throw $e;
            }
        } catch (\Exception $e) {
            $this->integration->storage()
                ->set('express', false);

            $this->handleError($e->getMessage());
        }
    }

    /*
     * add notice, redirect to cart / cancel order and clear easycredit storage 
     **/
    public function handleError($message)
    {
        $this->integration->logger()->error($message);
        wc_add_notice(__($message, 'wc-easycredit'), 'error');
        $this->integration->checkout()->clear();

        $url = wc_get_page_permalink('cart');

        $order = $this->get_current_order();
        if ($order) {
            $url = $order->get_cancel_order_url_raw();
        }
        wp_safe_redirect($url);
        exit;
    }

    public function authorizeAction($params)
    {
        $txId = $_GET['transactionId'];
        $secToken = $params['sec_token'];

        $args = [
            'post_type' => 'shop_order',
            'post_status' => 'any',
            'limit' => 1,
            'return' => 'ids',
            'meta_query' => [
                'relation' => 'AND',
                [
                    'key' => self::META_KEY_TRANSACTION_ID,
                    'value' => $txId,
                    'compare' => '=',
                ],
                [
                    'key' => 'ratenkaufbyeasycredit-sec-token',
                    'value' => $params['sec_token'],
                    'compare' => '=',
                ],
            ],
        ];

        $orders = new \WP_Query($args);
        $order = wc_get_order(current($orders->posts));

        if (!$order) {
            header('HTTP/1.1 404 Not Found');
            echo 'transaction not found';
            exit;
        }
        $token = $order->get_meta($this->id . '-token');
        if (!$token) {
            header('HTTP/1.1 404 Not Found');
            echo 'technical transaction id not found';
            exit;
        }

        $tx = $this->integration
            ->checkout()
            ->loadTransaction($token);

        if ($tx->getStatus() !== ApiV3\Model\TransactionInformation::STATUS_AUTHORIZED) {
            header('HTTP/1.1 409 Conflict');
            echo 'payment status of transaction not updated as transaction status is not AUTHORIZED';
            exit;
        }

        if ($order->payment_complete(
            $txId
        )) {
            header('HTTP/1.1 200 OK');
            echo 'payment status successfully set';
            exit;
        }

        header('HTTP/1.1 500 Internal Server Error');
        echo 'payment status could not be set, please check the logs';
        exit;
    }

    public function activate_single_site()
    {
        require_once(\WC_ABSPATH . 'includes/admin/wc-admin-functions.php');

        $pages = array_merge(
            Pages\ReviewPage::get_page_data(),
            Pages\InfoPage::get_page_data(),
        );
        foreach ($pages as $key => $page) {
            wc_create_page(
                esc_sql($page['name']),
                $key,
                $page['title'],
                $page['content']
            );
        }

        delete_transient('woocommerce_cache_excluded_uris');
    }

    public function get_current_order()
    {
        $order_id = $this->integration->storage()->get('order_id');
        if (!$order_id) {
            return false;
        }

        return wc_get_order($order_id);
    }

    public function load_textdomain()
    {
        load_plugin_textdomain(
            'wc-easycredit',
            false,
            basename(dirname($this->file)) . '/languages/'
        );
    }

    public function load_template($tpl, $data = [])
    {
        foreach ($data as $k => $v) {
            set_query_var($k, $v);
        }

        $template = $this->plugin_path . '/templates/' . $tpl . '.php';

        $_template = locate_template($tpl . '.php');
        if ($_template) {
            $template = $_template;
        }
        load_template($template, false);
    }

    public function add_module_nomodule_attribute($tag, $handle, $src)
    {
        if ($handle === 'easycredit-components-module') {
            $src = remove_query_arg('ver', $src);
            return '<script type="module" src="' . esc_url($src) . '"></script>';
        }
        if ($handle === 'easycredit-components-nomodule') {
            $src = remove_query_arg('ver', $src);
            return '<script nomodule src="' . esc_url($src) . '"></script>';
        }
        return $tag;
    }

    public function enqueue_easycredit_components()
    {
        wp_register_script('easycredit-components-module', 'https://invoice.easycredit-ratenkauf-webcomponents.pages.dev/easycredit-components/easycredit-components.esm.js', [], '1.0');
        wp_enqueue_script('easycredit-components-module');
        wp_register_script('easycredit-components-nomodule', 'https://invoice.easycredit-ratenkauf-webcomponents.pages.dev/easycredit-components/easycredit-components.js', [], '1.0');
        wp_enqueue_script('easycredit-components-nomodule');
        add_filter('script_loader_tag', [$this, 'add_module_nomodule_attribute'], 10, 3);
    }

    public function enqueue_frontend_resources($hook)
    {
        $this->enqueue_easycredit_components();

        wp_enqueue_script(
            'wc_easycredit_js',
            $this->plugin_url . 'assets/js/easycredit.min.js',
            ['jquery'],
            '2.1'
        );
        wp_enqueue_style(
            'wc_easycredit_css',
            $this->plugin_url . 'assets/css/easycredit.min.css'
        );
    }

    public function enqueue_backend_resources($hook)
    {
        $this->enqueue_easycredit_components();

        wp_enqueue_script(
            'wc_easycredit_js',
            $this->plugin_url . 'assets/js/easycredit-backend.js',
            ['jquery'],
            '1.0'
        );

        wp_localize_script('wc_easycredit_js', 'wc_easycredit_config', [
            'url' => admin_url('admin-post.php'),
        ]);

        wp_enqueue_style(
            'wc_easycredit_css',
            $this->plugin_url . 'assets/css/easycredit-backend.css'
        );
        wp_enqueue_style(
            'wc_easycredit_marketing_css',
            $this->plugin_url . 'assets/css/easycredit-backend-marketing.min.css'
        );

        wp_enqueue_media();
    }

    public function hook_prevent_shipping_address_change($box)
    {
        global $wp_meta_boxes;
        $wp_meta_boxes['shop_order']['normal']['high']['woocommerce-order-data']['callback'] = static::class . '::prevent_shipping_address_change';
    }

    public static function prevent_shipping_address_change($post)
    {
        /* @var WC_Order $theorder */
        global $theorder;

        if (!is_object($theorder)) {
            $theorder = wc_get_order($post->ID);
        }

        $order = $theorder;
        if (!is_object($order) || $order->get_payment_method() != 'easycredit') {
            \WC_Meta_Box_Order_Data::output($post);
            return;
        }

        $note = '<p>Die Versandadresse kann bei Zahlung über easyCredit nicht nachträglich verändert werden.</p>';

        ob_start();
        \WC_Meta_Box_Order_Data::output($post);
        $html = ob_get_contents();
        $html = preg_replace('/(<h3>.+?)(<a .+?class="edit_address">.+?<\/a>)(.+?load_customer_shipping.+?<\/h3>)/sU', '$1$3' . $note, (string)$html);
        ob_end_clean();
        echo $html;
    }
    
    public function verify_credentials()
    {
        $status = [
            'status' => true,
            'msg' => __('Credentials are valid! Your can now offer easyCredit payment in your store.', 'wc-easycredit'),
        ];

        $error = $this->check_credentials($_REQUEST['api_key'], $_REQUEST['api_token'], $_REQUEST['api_signature']);
        if ($error) {
            $status = [
                'status' => false,
                'msg' => strip_tags($error),
            ];
        }

        wp_send_json($status);
    }

    public function check_credentials($apiKey, $apiToken, $apiSignature = null)
    {
        if (!empty($apiKey) && !empty($apiToken)) {
            try {
                try {
                    $this->integration->checkout()->verifyCredentials($apiKey, $apiToken, $apiSignature);
                } catch (ApiV3\Integration\ApiCredentialsInvalidException $e) {
                    $settingsUri = admin_url('admin.php?page=wc-settings&tab=checkout&section=easycredit');
                    return implode(' ', [
                        __('easyCredit payment credentials are not valid.', 'wc-easycredit'),
                        sprintf(__('Please go to <a href="%s">plugin settings</a> and correct API Key and API Token.', 'wc-easycredit'), $settingsUri),
                    ]);
                } catch (ApiV3\Integration\ApiCredentialsNotActiveException $e) {
                    return __('Your credentials are valid, but your account has not been activated yet.', 'wc-easycredit');
                } catch (ApiV3\ApiException $e) {
                    if ($e->getResponseObject() instanceof ApiV3\Model\ConstraintViolation) {
                        $messages = [];
                        foreach ($e->getResponseObject()->getViolations() as $violation) {
                            $messages[] = implode(': ', [$violation->getField(), $violation->getMessage()]);
                        }
                        return implode(' ', [
                            __('easyCredit payment credentials are not valid.', 'wc-easycredit'),
                            sprintf(__('An error occured while checking your credentials: %s', 'wc-easycredit'), implode(', ', $messages)),
                        ]);
                    }
                    throw $e;
                }
            } catch (\Exception $e) {
                error_log($e->getMessage());
                return sprintf(__('An error occured while checking your credentials: %s', 'wc-easycredit'), $e->getMessage());
            }
        } else {
            return __('Please enter your credentials to use the easyCredit payment plugin in the <a href="%s">plugin settings</a>.', 'wc-easycredit');
        }
    }

    public function plugin_links($links)
    {
        $plugin_links = [
            '<a href="' . admin_url('admin.php?page=wc-settings&tab=checkout&section=easycredit') . '">' . __('Settings', 'wc-easycredit') . '</a>',
        ];
        return array_merge($plugin_links, $links);
    }

    public function apply_migrations()
    {
        global $wpdb;

        foreach (new \DirectoryIterator(__DIR__.'/../migrations') as $fileInfo) {
            if ($fileInfo->getExtension() !== 'php') {
                continue;
            }

            if (is_multisite()) {
                foreach ($wpdb->get_col("SELECT blog_id FROM {$wpdb->blogs}") as $blog_id) {
                    switch_to_blog($blog_id);
                    $this->migrate($fileInfo);
                    restore_current_blog();
                }
            } else {
                $this->migrate($fileInfo);
            }
        }
    }

    protected function migrate($fileInfo) {
        $migrationId = $fileInfo->getBasename('.' .$fileInfo->getExtension());
        if (!preg_match('/^\d+?-(.+?)$/',$migrationId, $matches)) {
            return;
        }
        list ($time, $slug) = $matches;

        if (!get_transient($slug)) {
            require_once $fileInfo->getPathname();
            set_transient($slug, true);
        }
    }

    public function is_easycredit_method ($method) {
        return strncmp($method, $this->id, strlen($this->id)) === 0;
    }
}
