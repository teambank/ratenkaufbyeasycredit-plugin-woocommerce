<?php
namespace Netzkollektiv\EasyCredit\Api;

class Quote implements \Netzkollektiv\EasyCreditApi\Rest\QuoteInterface {

    public function __construct(\WC_Order $order, \WC_Gateway_RatenkaufByEasyCredit $gateway) {
        $this->_gateway = $gateway;
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

    public function getIsClickAndCollect() {
        $shippingItem = current($this->_quote->get_items('shipping'));
        if ($shippingItem instanceof \WC_Order_Item_Shipping) {
            return $shippingItem->get_method_id() == $this->_gateway->get_option('clickandcollect_shipping_method');
        }
    }


    public function getGrandTotal() {
        return $this->_quote->get_total();
    }

    public function getBillingAddress() {
        $address = $this->_quote->get_address('billing');
        if (!array_filter($address) && $this->getCustomer()->isLoggedIn()) {
            $address = $this->_customer->get_billing();
        }

        return new Quote\Address($address);
    }

    public function getShippingAddress() {
        $_key = 'billing';
        if ($this->_quote->get_meta('ship_to_different_address')) {
            $_key = 'shipping';
        }

        $address = $this->_quote->get_address($_key);
        if (!array_filter($address) && $this->getCustomer()->isLoggedIn()) {
            $address = ($_key == 'billing') ? $this->_customer->get_billing() : $this->_customer->get_shipping();
        }
        return new Quote\ShippingAddress($address);
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
            if ($item->get_subtotal() == 0) {
                continue;
            }
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
