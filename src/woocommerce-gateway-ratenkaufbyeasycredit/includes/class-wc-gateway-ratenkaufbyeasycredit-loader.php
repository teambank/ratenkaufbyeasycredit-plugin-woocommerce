<?php
class WC_Gateway_Ratenkaufbyeasycredit_Loader {
    public function __construct($plugin) {

	    $this->includes_path = $plugin->includes_path;
	    $this->plugin_path = $plugin->plugin_path;
	    
        spl_autoload_register( array($this, 'autoloader') );

        require_once $plugin->includes_path.'/class-wc-gateway-ratenkaufbyeasycredit.php';
        require_once $plugin->includes_path.'/class-wc-gateway-ratenkaufbyeasycredit-widget.php';
        require_once $plugin->includes_path.'/class-wc-gateway-ratenkaufbyeasycredit-widget-cart.php';
        require_once $plugin->includes_path.'/class-wc-gateway-ratenkaufbyeasycredit-widget-product.php';

        add_filter( 'woocommerce_payment_gateways', array($this, 'payment_gateways') );
    }

    public function autoloader($class) {
    	$ds = DIRECTORY_SEPARATOR;
    	
        if ( false !== strpos( $class, 'EasyCreditApi' ) ) {
            $file = str_replace( array('_','Netzkollektiv\\','\\'), $ds, $class ) . '.php';
            require_once $this->plugin_path . $ds . 'lib' . $file;
            return;
        }
        
        if ( false !== strpos( $class, 'EasyCredit' ) ) {
            $file = str_replace( array('_','Netzkollektiv\\','\\'), $ds, $class ) . '.php';
            require_once $this->includes_path . $file;
            return;
        }
        
        if ( false !== strpos( $class, 'Zend' ) ) {
            $file = str_replace( array('_',), $ds, $class ) . '.php';
            $file = implode($ds,array(
                $this->plugin_path,
                'zend',
                'src',
                $file
            ));
            if (file_exists($file)) {
                require_once $file;
            }
            return;
        }
    }

    public function payment_gateways( $gateways ) {
        $gateways[] = 'WC_Gateway_RatenkaufByEasyCredit';
        return $gateways;
    }
}
