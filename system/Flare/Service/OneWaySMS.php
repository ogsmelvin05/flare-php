<?php

namespace Flare\Service;

use Flare\Service\OneWaySMS\Result;
use Flare\Http\Client\Curl\Request;
use Flare\Http\Client\Curl;
use Flare\Service;

/**
 * 
 * @author anthony
 * 
 */
class OneWaySMS extends Service
{   
    /**
     * 
     * @var string
     */
    private $_username;

    /**
     * 
     * @var string
     */
    private $_password;

    /**
     * 
     * @var string
     */
    private $_host = 'gateway.onewaysms.ph';

    /**
     * 
     * @var string
     */
    private $_port = '10001';

    /**
     * 
     * @param array $config
     * @return void
     */
    protected function init(array $config)
    {
        if (isset($config['username'], $config['password'])) {
            $this->_username = $config['username'];
            $this->_password = $config['password'];
            if (!empty($config['host'])) {
                $this->_host = rtrim($config['host'], '/');
            }
            if (!empty($config['port'])) {
                $this->_port = ltrim((string) $config['port'], ':');
            }
        } else {
            show_error('Username and password is required for OneWaySMS service');
        }
    }

    /**
     * 
     * @param string $from
     * @param string $to
     * @param string $message
     * @param int $languagetype
     * @return \Flare\Service\OneWaySMS\Result
     */
    public function send($from, $to, $message, $languagetype = 1)
    {
        $request = new Request($this->_getApiHost().'api.aspx');
        $request->setParam('senderid', $from)
            ->setParam('mobileno', $to)
            ->setParam('message', $message)
            ->setParam('languagetype', $languagetype)
            ->setParam('apiusername', $this->_username)
            ->setParam('apipassword', $this->_password);

        $errorCode = null;
        $errorMessage = null;
        $response = Curl::execute($request);
        
        if ($response->hasError()) {
            $errorCode = $response->getErrorCode();
            $errorMessage = $response->getError();
        } elseif ($response->getBody() < 0) {
            $errorCode = $response->getBody();
            $errorMessage = Result::$errors[Result::SEND_SMS][$errorCode];
        }

        return new Result($response->getBody(), Result::SEND_SMS, $errorCode, $errorMessage);
    }

    /**
     * 
     * @param string $mtid
     * @return \Flare\Service\OneWaySMS\Result
     */
    public function getTransaction($mtid)
    {
        $request = new Request($this->_getApiHost().'bulktrx.aspx');
        $request->setParam('mtid', $mtid);

        $errorCode = null;
        $errorMessage = null;
        $response = Curl::execute($request);

        if ($response->hasError()) {
            $errorCode = $response->getErrorCode();
            $errorMessage = $response->getError();
        } elseif ($response->getBody() < 0) {
            $errorCode = $response->getBody();
            $errorMessage = Result::$errors[Result::TRANSACTION_STATUS][$errorCode];
        }

        return new Result(!$errorCode && !$errorMessage, Result::TRANSACTION_STATUS, $errorCode, $errorMessage);
    }

    /**
     * 
     * @return \Flare\Service\OneWaySMS\Result
     */
    public function getCreditBalance()
    {
        $request = new Request($this->_getApiHost().'bulkcredit.aspx');
        $request->setParam('apiusername', $this->_username)
            ->setParam('apipassword', $this->_password);

        $errorCode = null;
        $errorMessage = null;
        $response = Curl::execute($request);

        if ($response->hasError()) {
            $errorCode = $response->getErrorCode();
            $errorMessage = $response->getError();
        } elseif ($response->getBody() < 0) {
            $errorCode = $response->getBody();
            $errorMessage = Result::$errors[Result::CREDIT_BALANCE][$errorCode];
        }

        return new Result($response->getBody(), Result::CREDIT_BALANCE, $errorCode, $errorMessage);
    }

    /**
     * 
     * @return string
     */
    private function _getApiHost()
    {
        return 'http://'.$this->_host.':'.$this->_port.'/';
    }

    /**
     * 
     * @return string
     */
    public function getUsername()
    {
        return $this->_username;
    }

    /**
     * 
     * @return string
     */
    public function getHost()
    {
        return $this->_host;
    }

    /**
     * 
     * @return string
     */
    public function getPort()
    {
        return $this->_port;
    }
}