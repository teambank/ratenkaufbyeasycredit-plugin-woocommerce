<?php
namespace Netzkollektiv\EasyCredit\Api\Quote;

use Magento\Sales\Model\ResourceModel\Order\Collection as SalesOrderCollection;
use Magento\Customer\Model\Session as CustomerSession;

class Customer implements \Netzkollektiv\EasyCreditApi\Rest\CustomerInterface
{
    protected $_order = null;
    protected $_customer = null;

    public function __construct(
        \WC_Order $quote,
        $customer
    ) {
        $this->_quote = $quote;
        $this->_customer = $customer;
    }

    public function getPrefix() {
        return $this->_quote->get_meta('ratenkaufbyeasycredit-prefix');
    }

    public function getFirstname() {
        if (!$this->isLoggedIn()) {
            return $this->_quote->get_address('billing')['first_name'];
        }
        return $this->_customer->get_first_name();
    }

    public function getLastname() {
        if (!$this->isLoggedIn()) {
            return $this->_quote->get_address('billing')['last_name'];
        }
        return $this->_customer->get_last_name();
    }

    public function getEmail() {
        return $this->_quote->get_address('billing')['email'];
    }

    public function getDob() {
    	return null;
    }

    public function getCompany() {
        return $this->_quote->get_address('billing')['company'];
    }

    public function getTelephone() {
        return $this->_quote->get_billing_phone();
    }

    public function isLoggedIn() {
        return ($this->_customer !== false && $this->_customer->get_id());
    }

    public function getCreatedAt() {
        return (string)$this->_customer->get_date_created();
    }

        /*$billingAddress = $quote->getBillingAddress();

        if ($this->_customerSession->isLoggedIn()) {
            $customer = $quote->getCustomer();
            $customerData = $this->_convertPersonalData($customer);
            $email = $customer->getEmail();
        } else {
            $email = $billingAddress->getEmail();
            $customerData = $this->_convertPersonalDataFromBillingAddress($billingAddress);
        }*/


    public function getOrderCount() {
        if (!$this->isLoggedIn()) {
            return 0;
        }

        $query = new \WP_Query();
        $query->query(array(
            'numberposts' => -1,
            'meta_key'    => '_customer_user',
            'meta_value'  => $this->_customer->get_id(),
            'post_type'   => wc_get_order_types(),
            'post_status' => array_keys( wc_get_order_statuses() ),
        ));
        return $query->found_posts;
    }
}
