<?php
/**
 * Plugin Name:     ratenkauf by easyCredit for WooCommerce
 * Plugin URI:      https://www.easycredit-ratenkauf.de/
 * Description:     ratenkauf by easyCredit - use the easiest installment purchase of Germany for your WooCommerce store now
 * Author:          NETZKOLLEKTIV
 * Author URI:      https://netzkollektiv.com
 * License:         GPL2
 * License URI:     https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:     woocommerce-gateway-ratenkaufbyeasycredit
 * Domain Path:     /languages
 * Version:         1.7.0
 * Requires at least: 4.4
 * Tested up to: 5.7
 * WC requires at least: 3.0.0
 * WC tested up to: 4.9.1
 *
 * @package         Woocommerce_Gateway_Ratenkaufbyeasycredit
 */

defined( 'ABSPATH' ) or exit;

define( 'WC_RATENKAUFBYEASYCREDIT_VERSION', '1.7.0' );
define( 'WC_RATENKAUFBYEASYCREDIT_ID', 'ratenkaufbyeasycredit' );

function wc_ratenkaufbyeasycredit() {
    static $plugin;

    if ( ! isset( $plugin ) ) {
        require_once( dirname(__FILE__).'/includes/class-wc-gateway-ratenkaufbyeasycredit-plugin.php' );

        $plugin = new WC_Gateway_Ratenkaufbyeasycredit_Plugin(
            __FILE__,
            WC_RATENKAUFBYEASYCREDIT_VERSION
        );
    }

    return $plugin;
}

function ratenkaufByEasyCreditCheckForWooCommerce($plugin) {
    return preg_match('/^woocommerce[\-\.0-9]*\/woocommerce.php$/', $plugin);
}

$sitewidePlugins = is_array(get_site_option('active_sitewide_plugins')) ? get_site_option('active_sitewide_plugins') : array();
if (array_filter(
    array_merge(
        apply_filters( 'active_plugins',get_option( 'active_plugins' )),
        array_keys($sitewidePlugins)
    ),
    'ratenkaufByEasyCreditCheckForWooCommerce',ARRAY_FILTER_USE_BOTH
)) {
    wc_ratenkaufbyeasycredit()->maybe_run();
}
