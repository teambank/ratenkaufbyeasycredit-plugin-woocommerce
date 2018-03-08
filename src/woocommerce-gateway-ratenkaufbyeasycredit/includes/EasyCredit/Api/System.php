<?php
namespace Netzkollektiv\EasyCredit\Api;

class System implements \Netzkollektiv\EasyCreditApi\SystemInterface {

    public function getSystemVendor() {
        global $wp_version;
        return 'Wordpress '.$wp_version.', wooCommerce';
    }

    public function getSystemVersion() {
        if ( class_exists( '\WooCommerce' ) ) {
            global $woocommerce;
            return $woocommerce->version;
        }
    }

    public function getModuleVersion() {
        return \WC_RATENKAUFBYEASYCREDIT_VERSION;
    }

    public function getIntegration() {
        return 'PAYMENT_PAGE';
    }
}
