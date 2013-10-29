<?php

namespace Flare\Http\Client\Curl;

use Flare\Http\AbstractResponse;
use Flare\Object\Json;
use Flare\Object\Xml;

/**
 * 
 * @author anthony
 * 
 */
class Response extends AbstractResponse
{
    /**
     * 
     * @var string
     */
    private $body;

    /**
     * 
     * @var array
     */
    private $info;

    /**
     * 
     * @var array
     */
    private $headers;

    /**
     * 
     * @var string
     */
    private $errorMsg;

    /**
     * 
     * @var int
     */
    private $errorCode;

    /**
     * 
     * @param string $body
     * @param int $status
     * @param string $headers
     * @param int $errorCode
     * @param string $errorMsg
     */
    public function __construct($body, $info, $headers, $errorCode, $errorMsg)
    {
        $this->body = $body;
        $this->info = $info;
        $this->headers = $headers;
        $this->errorMsg = $errorMsg;
        $this->errorCode = $errorCode;
        if (isset($this->info['http_code'])) {
            $this->setStatusCode($this->info['http_code']);
        }
    }

    /**
     * 
     * @param string $key
     * @return mixed
     */
    public function __get($key)
    {
        return isset($this->info[$key]) ? $this->info[$key] : show_error("Undefined '{$key}' in CURL response");
    }

    /**
     * 
     * @param string $key
     * @return boolean
     */
    public function __isset($key)
    {
        return isset($this->info[$key]);
    }

    /**
     * 
     * @return array
     */
    public function getInfo()
    {
        return $this->info;
    }

    /**
     * 
     * @param string $key
     * @return string
     */
    public function getHeader($key)
    {
        return isset($this->headers[$key]) ? $this->headers[$key] : null;
    }

    /**
     * 
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * 
     * @return boolean
     */
    public function hasError()
    {
        return $this->errorMsg || $this->errorCode;
    }

    /**
     * 
     * @return string
     */
    public function getError()
    {
        return $this->errorMsg;
    }

    /**
     * 
     * @return int
     */
    public function getErrorCode()
    {
        return $this->errorCode;
    }

    /**
     * 
     * @return string
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * 
     * @return \Flare\Object\Json
     */
    public function getBodyAsJson()
    {
        return new Json($this->getBody());
    }

    /**
     * 
     * @return \Flare\Object\Xml
     */
    public function getBodyAsXml()
    {
        return new Xml($this->getBody());
    }
}