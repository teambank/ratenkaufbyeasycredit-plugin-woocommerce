<?php
/*
 * (c) NETZKOLLEKTIV GmbH <kontakt@netzkollektiv.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

if (!defined('ABSPATH')) {
    exit;
}

class WC_Gateway_Ratenkaufbyeasycredit_Loader
{
    protected $includes_path;
    protected $plugin_path;

    public function __construct($plugin)
    {
        $this->includes_path = $plugin->includes_path;
        $this->plugin_path = $plugin->plugin_path;

        require_once dirname(__FILE__) . '/../vendor/autoload.php';
        spl_autoload_register([$this, 'autoloader']);

        require_once $plugin->includes_path . '/class-wc-gateway-ratenkaufbyeasycredit.php';
        require_once $plugin->includes_path . '/class-wc-gateway-ratenkaufbyeasycredit-widget.php';
        require_once $plugin->includes_path . '/class-wc-gateway-ratenkaufbyeasycredit-widget-cart.php';
        require_once $plugin->includes_path . '/class-wc-gateway-ratenkaufbyeasycredit-widget-product.php';
        require_once $plugin->includes_path . '/class-wc-gateway-ratenkaufbyeasycredit-marketing.php';
        require_once $plugin->includes_path . '/class-wc-gateway-ratenkaufbyeasycredit-marketing-blocks.php';
        require_once $plugin->includes_path . '/class-wc-gateway-ratenkaufbyeasycredit-express-checkout.php';
        require_once $plugin->includes_path . '/order-management.php';
        require_once $plugin->includes_path . '/rest-api.php';

        add_filter('woocommerce_payment_gateways', [$this, 'payment_gateways']);
    }

    public function autoloader($class)
    {
        $ds = DIRECTORY_SEPARATOR;

        if (mb_strpos($class, 'Netzkollektiv\EasyCredit') === 0) {
            $file = str_replace(['_', 'Netzkollektiv\\', '\\'], $ds, $class) . '.php';
            if (file_exists($this->includes_path . $file)) {
                require_once $this->includes_path . $file;
                return;
            }
        }
    }

    public function payment_gateways($gateways)
    {
        $gateways[] = 'WC_Gateway_RatenkaufByEasyCredit';
        return $gateways;
    }
}
