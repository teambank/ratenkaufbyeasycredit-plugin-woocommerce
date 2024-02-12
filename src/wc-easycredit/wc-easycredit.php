<?php

/**
 * Plugin Name:     easyCredit for WooCommerce
 * Plugin URI:      https://www.easycredit-ratenkauf.de/
 * Description:     easyCredit - use the easiest pay later and installment purchase of Germany for your WooCommerce store now
 * Author:          NETZKOLLEKTIV
 * Author URI:      https://netzkollektiv.com
 * License:         GPL2
 * License URI:     https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:     wc-easycredit
 * Domain Path:     /languages
 * Version:         2.1.4
 * Requires at least: 4.4
 * Tested up to: 6.2
 * WC requires at least: 3.0.0
 * WC tested up to: 7.8.0
 *
 */

defined('ABSPATH') or exit;

define('WC_EASYCREDIT_VERSION', '2.1.4');
define('WC_EASYCREDIT_ID', 'easycredit');

use Netzkollektiv\EasyCredit\Plugin;

function wc_easycredit()
{
    static $plugin;

    if (!isset($plugin)) {

        require_once dirname(__FILE__) . '/vendor/autoload.php';

        spl_autoload_register(function ($class) {
            $ds = DIRECTORY_SEPARATOR;
            $includesPath = plugin_dir_path(__FILE__) . 'includes';
            if (mb_strpos($class, 'Netzkollektiv\EasyCredit') === 0) {

                $file = str_replace(['_', 'Netzkollektiv\\EasyCredit\\', '\\'], $ds, $class) . '.php';
                if (file_exists($includesPath . $file)) {
                    require_once $includesPath . $file;
                    return;
                }
            }
        });

        $plugin = new Plugin(
            __FILE__
        );
    }

    return $plugin;
}

function easyCreditCheckForWooCommerce($plugin)
{
    return preg_match('/^woocommerce[\-\.0-9]*\/woocommerce.php$/', $plugin);
}

$sitewidePlugins = is_array(get_site_option('active_sitewide_plugins')) ? get_site_option('active_sitewide_plugins') : [];
if (array_filter(
    array_merge(
        apply_filters('active_plugins', get_option('active_plugins')),
        array_keys($sitewidePlugins)
    ),
    'easyCreditCheckForWooCommerce',
    ARRAY_FILTER_USE_BOTH
)) {
    wc_easycredit()->maybe_run();
}