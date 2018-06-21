<?php
namespace Netzkollektiv\EasyCredit\Api;

class Logger implements \Netzkollektiv\EasyCreditApi\LoggerInterface {

    protected $_logger;
    protected $_gateway;

    protected $debug = false;

    public function __construct(\WC_Settings_API $gateway) {
        if ($gateway->get_option('debug') == 'yes') {
            $this->debug = true;
        }
        $this->_gateway = $gateway;
        $this->_logger = wc_get_logger();
    }

    public function log($msg) {
        if (!$this->debug) {
            return;
        }

        return $this->logInfo($this->_format($msg), array( 'source' => $this->_gateway->id ));
    }

    public function logDebug($msg) {
        if (!$this->debug) {
            return;
        }

        $this->_logger->debug($this->_format($msg), array( 'source' => $this->_gateway->id ));
        return $this;
    }

    public function logInfo($msg) {
        if (!$this->debug) {
            return;
        }
        
        $this->_logger->info($this->_format($msg), array( 'source' => $this->_gateway->id ));
        return $this;
    }

    public function logWarn($msg) {
        if (!$this->debug) {
            return;
        }

        $this->_logger->warning($this->_format($msg), array( 'source' => $this->_gateway->id ));
        return $this;
    }

    public function logError($msg) {
        $this->_logger->emergency($this->_format($msg), array( 'source' => $this->_gateway->id ));
        return $this;
    }

    protected function _format($msg) {
        if (is_array($msg) || is_object($msg)) {
            $msg = print_r($msg, true);
        }
        return $msg;
    }
}
