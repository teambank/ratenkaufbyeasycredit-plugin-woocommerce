<?php
namespace Netzkollektiv\EasyCredit\Api;

class Storage implements \Netzkollektiv\EasyCreditApi\StorageInterface {

    protected $key = 'wc_ratenkaufbyeasycredit'; 
    protected $session;

    public function __construct() {
        $this->session = WC()->session;
    }

    public function set($key, $value) {

        $data = $this->session->get($this->key);
        if (!is_array($data)) {
            $data = array();
        }
        $data[$key] = $value;
        $this->session->set($this->key, $data);

        return $this;
    }

    public function get($key) {
        $data = $this->session->get($this->key);
        if (isset($data[$key])) {
            return $data[$key];
        }
    }

    public function clear() {
        $this->session->set($this->key, null);
    }
}
