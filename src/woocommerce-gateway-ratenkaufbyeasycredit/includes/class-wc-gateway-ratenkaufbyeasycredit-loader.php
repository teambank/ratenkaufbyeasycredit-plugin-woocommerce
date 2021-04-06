<?php
class WC_Gateway_Ratenkaufbyeasycredit_Loader {
    public function __construct($plugin) {

	    $this->includes_path = $plugin->includes_path;
	    $this->plugin_path = $plugin->plugin_path;

        require_once dirname(__FILE__).'/../vendor/autoload.php';
        spl_autoload_register( array($this, 'autoloader') );

        require_once $plugin->includes_path.'/class-wc-gateway-ratenkaufbyeasycredit.php';
        require_once $plugin->includes_path.'/class-wc-gateway-ratenkaufbyeasycredit-widget.php';
        require_once $plugin->includes_path.'/class-wc-gateway-ratenkaufbyeasycredit-widget-cart.php';
        require_once $plugin->includes_path.'/class-wc-gateway-ratenkaufbyeasycredit-widget-product.php';
        require_once $plugin->includes_path.'/order-management.php';
        require_once $plugin->includes_path.'/rest-api.php';

        add_filter( 'woocommerce_payment_gateways', array($this, 'payment_gateways') );
    }

    public function autoloader($class) {
    	$ds = DIRECTORY_SEPARATOR;

        if ( false !== strpos( $class, 'EasyCredit' ) ) {
            $file = str_replace( array('_','Netzkollektiv\\','\\'), $ds, $class ) . '.php';
            require_once $this->includes_path . $file;
            return;
        }
    }

    public function payment_gateways( $gateways ) {
        $gateways[] = 'WC_Gateway_RatenkaufByEasyCredit';
        return $gateways;
    }
}
