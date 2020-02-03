<?php
class WC_Gateway_Ratenkaufbyeasycredit_RestApi {
    protected $_field = 'ratenkaufbyeasycredit-merchant-status';

    public function __construct($plugin, $order_management) {
    	$this->plugin = $plugin;
    	$this->plugin_url = $plugin->plugin_url;
        $this->gateway = $this->plugin->get_gateway();

        $this->order_management = $order_management;

        if ( ! is_user_logged_in() ||
            ! current_user_can( 'shop_manager' ) 
        ) {
            //return;
        }

        $this->register_routes();
    }
    
    public function register_routes() {
        register_rest_route( 'easycredit/v1', '/transactions', array(
            'methods' => 'GET',
            'callback' => array( $this, 'get_transactions' )
        ));

        register_rest_route( 'easycredit/v1', '/transaction', array(
            'methods' => 'GET',
            'callback' => array( $this, 'get_transaction' )
        ));

        register_rest_route( 'easycredit/v1', '/transaction', array(
            'methods' => 'POST',
            'callback' => array( $this->order_management, 'set_status' )
        ));
    }

    public function get_transactions() {
        $txs = array();
        foreach ($this->order_management->get_transactions() as $transaction) {
            $txs[] = json_decode($transaction->transaction);
        }
        return $txs;
    }

    public function get_transaction(WP_REST_Request $request) {
        $id = $request->get_param('id');
        $transaction = current($this->order_management->get_transactions($id));
        if ($transaction->transaction) {
            return json_decode($transaction->transaction);
        }
    }
}