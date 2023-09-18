<?php
/*
 * (c) NETZKOLLEKTIV GmbH <kontakt@netzkollektiv.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

if (!defined('ABSPATH')) {
    exit;
}

class WC_Gateway_Ratenkaufbyeasycredit_Marketing_Blocks
{
    protected $plugin;
    protected $plugin_url;

    public function __construct($plugin)
    {
        $this->plugin = $plugin;
        $this->plugin_url = $plugin->plugin_url;

        add_filter( 'block_categories_all', [$this, 'block_categories']);
        add_action( 'init', [$this, 'register_block']);
        add_action( 'wp_enqueue_scripts', [$this, 'set_block_script_translations'], 100);
        add_action( 'admin_head', [$this, 'add_url_var']);
    }

    public function block_categories( $categories )
    {
    	$categories[] = array(
	    	'slug'  => 'easycredit-ratenkauf',
		    'title' => 'easyCredit-Ratenkauf'
	    );

    	return $categories;
    }

    public function register_block()
    {
        register_block_type( dirname(__FILE__) . '/../blocks/block.json', [
            'title'           => __( 'Marketing Card', 'woocommerce-gateway-ratenkaufbyeasycredit' ),
		    'description'     => __( 'The card component advertises easyCredit-Ratenkauf. The default image shown in the card can be overwritten. This allows you to use an image that matches your product offering.', 'woocommerce-gateway-ratenkaufbyeasycredit' )
        ]);
    }

    public function set_block_script_translations()
    {
        wp_set_script_translations( 'easycredit-ratenkauf-marketing-card-editor-script', 'woocommerce-gateway-ratenkaufbyeasycredit' );
    }

    public function add_url_var()
    {
        $outputs = '<script type="text/javascript">';
        $outputs .= 'var ecPluginUrl = ' . json_encode( $this->plugin_url ) . ';';
        $outputs .= '</script>';

        echo $outputs;
    }
}
