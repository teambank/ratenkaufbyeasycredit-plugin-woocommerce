<?php
class WC_Gateway_Ratenkaufbyeasycredit_RestApi {
    protected $_field = 'ratenkaufbyeasycredit-merchant-status';

    public function __construct($plugin, $order_management) {
    	$this->plugin = $plugin;
    	$this->plugin_url = $plugin->plugin_url;
        $this->gateway = $this->plugin->get_gateway();

        $this->order_management = $order_management;

        if (is_user_logged_in() && (
            current_user_can( 'shop_manager' )
			|| current_user_can('administrator')
        )) {
			$this->register_routes();
        }

    }
    
    public function register_routes() {
        register_rest_route( 'easycredit/v1', '/transactions', array(
            'methods' => 'GET',
            'callback' => array( $this, 'get_transactions' ),
            'permission_callback' => '__return_true' // // allow for anybody as routes are only registered in admin
        ));

        register_rest_route( 'easycredit/v1', '/transaction', array(
            'methods' => 'GET',
            'callback' => array( $this, 'get_transaction' ),
            'permission_callback' => '__return_true' // // allow for anybody as routes are only registered in admin
        ));

        register_rest_route( 'easycredit/v1', '/transaction', array(
            'methods' => 'POST',
            'callback' => array( $this, 'update_transaction' ),
            'permission_callback' => '__return_true' // allow for anybody as routes are only registered in admin
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
            $trans = json_decode($transaction->transaction);
            return $trans;
        }
    }

    public function update_transaction(WP_REST_Request $request) {
        $params = $request->get_json_params();

        $client = $this->gateway->get_merchant_client();

        switch ($params['status']) {
            case "LIEFERUNG":
                $client->confirmShipment($params['id']);
                break;
            case "WIDERRUF_VOLLSTAENDIG":
            case "WIDERRUF_TEILWEISE":
            case "RUECKGABE_GARANTIE_GEWAEHRLEISTUNG":
            case "MINDERUNG_GARANTIE_GEWAEHRLEISTUNG":
                $client->cancelOrder(
                    $params['id'], 
                    $params['status'], 
                    DateTime::createFromFormat('Y-d-m', $params['date']), 
                    $params['amount']
                );
                break;
        }

        $cachedTransaction = current($this->order_management->get_transactions($params['id']));
        if ($cachedTransaction->post_id) {
            $transaction = current($client->getTransaction($params['id']));
            update_post_meta($cachedTransaction->post_id, $this->_field, json_encode($transaction));
        }
    }
}
