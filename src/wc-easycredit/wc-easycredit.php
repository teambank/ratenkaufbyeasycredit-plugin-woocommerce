<?php
/**
 * Plugin Name:     easyCredit-Ratenkauf for WooCommerce
 * Plugin URI:      https://www.easycredit-ratenkauf.de/
 * Description:     easyCredit-Ratenkauf - use the easiest installment purchase of Germany for your WooCommerce store now
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

function wc_easycredit()
{
    static $plugin;

    if (!isset($plugin)) {
        require_once(dirname(__FILE__) . '/includes/class-plugin.php');

        $plugin = new WC_Easycredit_Plugin(
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
