<?php
namespace Netzkollektiv\EasyCredit\Api;

class SystemBuilder {

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

    public function build () {
        return new \Teambank\RatenkaufByEasyCreditApiV3\Model\Shopsystem([
            'shopSystemManufacturer' => implode(' ',[$this->getSystemVendor(),$this->getSystemVersion()]),
            'shopSystemModuleVersion' => $this->getModuleVersion()
        ]);
    }
}
