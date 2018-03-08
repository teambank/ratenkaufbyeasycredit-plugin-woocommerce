<?php
namespace Netzkollektiv\EasyCredit\Api\Quote;

class Item implements \Netzkollektiv\EasyCreditApi\Rest\ItemInterface {

    protected $_item = null;

    public function __construct(\WC_Order_Item_Product $item) {
        $this->_item = $item;
        $this->_product = $item->get_product();
    }

    public function getSku() {
        return $this->_item->get_product_id();
    }

    public function getName() {
        return $this->_item->get_name();
    }

    public function getQty() {
        return $this->_item->get_quantity();
    }

    public function getPrice() {
        return $this->_item->get_subtotal();
    }

    public function getManufacturer() {
        return '';
    }

    public function getCategory() {
        $term_list = \wp_get_post_terms($this->_product->get_id(),'product_cat',array('fields'=>'ids'));
        $term = \get_term (current($term_list), 'product_cat');
        if ($term instanceof \WP_Term) {
            return $term->name;
        }
    }
}
