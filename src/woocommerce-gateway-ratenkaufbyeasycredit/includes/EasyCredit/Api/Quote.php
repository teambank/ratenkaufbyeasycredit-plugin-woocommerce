<?php
namespace Netzkollektiv\EasyCredit\Api;

class Quote implements \Netzkollektiv\EasyCreditApi\Rest\QuoteInterface {

    public function __construct(\WC_Order $order) {

        $this->_quote = $order;
        $this->_customer = new \WC_Customer( $order->get_user_id() );
    }

    public function getId() {
        return $this->_quote->get_order_key();
    }

    public function getShippingMethod() {
        $shippingItem = current($this->_quote->get_items('shipping'));
        if ($shippingItem instanceof \WC_Order_Item_Shipping) {
            return $shippingItem->get_name();
        }
    }

    public function getGrandTotal() {
        return $this->_quote->get_total();
    }

    public function getBillingAddress() {
        return new Quote\Address($this->_quote->get_address('billing'));
    }
    public function getShippingAddress() {
        $_key = 'billing';
        if ($this->_quote->get_meta('ship_to_different_address')) {
            $_key = 'shipping';
        }
        return new Quote\ShippingAddress($this->_quote->get_address($_key));
    }

    public function getCustomer() {
        return new Quote\Customer(
            $this->_quote,
            $this->_customer
        );
    }

    public function getItems() {
        return $this->_getItems(
            $this->_quote->get_items()
        );
    }

    protected function _getItems($items) {
        $_items = array();
        foreach ($items as $item) {
            $_items[] = new Quote\Item(
                $item
            );
        }
        return $_items;
    }

    public function getSystem() {
        return new System();
    }
}
