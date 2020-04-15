<?php
/**
 * Plugin Name:     ratenkauf by easyCredit for WooCommerce
 * Plugin URI:      https://www.easycredit-ratenkauf.de/
 * Description:     ratenkauf by easyCredit - jetzt die einfachste Teilzahlungslösung Deutschlands mit WooCommerce nutzen.
 * Author:          NETZKOLLEKTIV
 * Author URI:      https://netzkollektiv.com
 * License:         GPL2
 * License URI:     https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:     woocommerce-gateway-ratenkaufbyeasycredit
 * Domain Path:     /languages
 * Version:         1.6.1
 * WC requires at least: 3.0.0
 * WC tested up to: 4.0.0
 *
 * @package         Woocommerce_Gateway_Ratenkaufbyeasycredit
 */

defined( 'ABSPATH' ) or exit;

define( 'WC_RATENKAUFBYEASYCREDIT_VERSION', '1.6.1' );
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

if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
    wc_ratenkaufbyeasycredit()->maybe_run();
}
