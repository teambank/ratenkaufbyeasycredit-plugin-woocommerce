<?php
namespace Netzkollektiv\EasyCredit\Api\Quote;

class Address implements \Netzkollektiv\EasyCreditApi\Rest\AddressInterface {
    protected $_address = array();

    public function __construct($address) {
        $this->_address = $address;
    }

    public function getFirstname() {
        return $this->_address['first_name'];
    }

    public function getLastname() {
        return $this->_address['last_name'];
    }

    public function getStreet() {
        return $this->_address['address_1'];
    }

    public function getStreetAdditional() {
        return isset($this->_address['address_2']) && !empty($this->_address['address_2']) ? $this->_address['address_2'] : null;
    }

    public function getPostcode() {
        return $this->_address['postcode'];
    }

    public function getCity() {
        return $this->_address['city'];
    }

    public function getCountryId() {
        return $this->_address['country'];
    }
}
