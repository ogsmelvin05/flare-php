<?php

namespace Flare\Http\Client\Curl;

use Flare\Http\AbstractRequest;

/**
 * 
 * @author anthony
 * 
 */
class Request extends AbstractRequest
{
    /**
     * 
     * @var string
     */
    private $method;

    /**
     * 
     * @var array
     */
    private $params = array();

    /**
     * 
     * @var array
     */
    private $options;

    /**
     * 
     * @var array
     */
    private static $defaultOptions = array(
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HEADER => true
    );

    /**
     * 
     * @var boolean
     */
    private $headerOut = true;

    /**
     * 
     * @param string $url
     * @param string $method
     * @param array $options
     */
    public function __construct($url, $method = self::METHOD_GET, array $options = array())
    {
        $this->setUri($url);
        $this->setMethod($method);
        if ($options) {
            $this->setOptions(array_merge(self::$defaultOptions, $options));
        } else {
            $this->setOptions(self::$defaultOptions);
        }
    }

    /**
     * 
     * @param boolean $switch
     * @return \Flare\Http\Client\Curl\Request
     */
    public function setHeaderOut($switch)
    {
        $this->headerOut = $switch;
        return $this;
    }

    /**
     * 
     * @return boolean
     */
    public function isHeaderOut()
    {
        return $this->headerOut;
    }

    /**
     * 
     * @param string|int $key
     * @param mixed $value
     * @return \Flare\Http\Client\Curl\Request
     */
    public function setOption($key, $value)
    {
        if (is_string($key) && strpos($key, 'CURLOPT_') !== 0) {
            $key = constant('CURLOPT_'.strtoupper($key));
        }
        if ($key === CURLOPT_URL) {
            $this->url = $value;
        }
        $this->options[$key] = $value;
        return $this;
    }

    /**
     * 
     * @param array $options
     * @return \Flare\Http\Client\Curl\Request
     */
    public function setOptions(array $options)
    {
        foreach ($options as $key => $option) {
            $this->setOption($key, $option);
        }
        return $this;
    }

    /**
     * 
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * 
     * @param string|int $key
     * @return mixed
     */
    public function getOption($key)
    {
        if (is_string($key) && strpos($key, 'CURLOPT_') !== 0) {
            $key = constant('CURLOPT_'.strtoupper($key));
        }
        return isset($this->options[$key]) ? $this->options[$key] : null;
    }

    /**
     * 
     * @param string $type
     * @return \Flare\Http\Client\Curl\Request
     */
    public function setContentType($type)
    {
        $this->setHttpHeader('Content-Type', $type);
        return $this;
    }

    /**
     * 
     * @return string
     */
    public function getContentType()
    {
        return $this->getHttpHeader('Content-Type');
    }

    /**
     * 
     * @param string $url
     * @return \Flare\Http\Client\Curl\Request
     */
    public function setUri($url)
    {
        $this->setOption('url', $url);
        return $this;
    }

    /**
     * 
     * @return string
     */
    public function getUri()
    {
        return $this->getOption('url');
    }

    /**
     * 
     * @param string $name
     * @param mixed $value
     * @return \Flare\Http\Client\Curl\Request
     */
    public function setParam($name, $value)
    {
        $this->params[$name] = $value;
        return $this;
    }

    /**
     * 
     * @param string $name
     * @return mixed
     */
    public function getParam($name)
    {
        return isset($this->params[$name]) ? $this->params[$name] : null;
    }

    /**
     * 
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * 
     * @param array $params
     * @return \Flare\Http\Client\Curl\Request
     */
    public function setParams(array $params)
    {
        foreach ($params as $key => $value) {
            $this->setParam($key, $value);
        }
        return $this;
    }

    /**
     * 
     * @param array $headers
     * @return \Flare\Http\Client\Curl\Request
     */
    public function setHttpHeaders(array $headers)
    {
        foreach ($headers as $key => $header) {
            $this->setHttpHeader($key, $header);
        }
        return $this;
    }

    /**
     * 
     * @param string $key
     * @param string $value
     * @return \Flare\Http\Client\Curl\Request
     */
    public function setHttpHeader($key, $value)
    {
        $this->options[CURLOPT_HTTPHEADER][$key] = $value;
        return $this;
    }

    /**
     * 
     * @return array
     */
    public function getHttpHeaders()
    {
        return $this->getOption('httpheader');
    }

    /**
     * 
     * @param string $key
     * @return string
     */
    public function getHttpHeader($key)
    {
        return isset($this->options[CURLOPT_HTTPHEADER][$key]) ? $this->options[CURLOPT_HTTPHEADER][$key] : null;
    }

    /**
     * 
     * @param string $method
     * @return \Flare\Http\Client\Curl\Request
     */
    public function setMethod($method)
    {
        $this->method = strtoupper($method);
        return $this;
    }

    /**
     * 
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * 
     * @return \Flare\Http\Client\Curl\Request
     */
    public function reset()
    {
        $this->params = array();
        $this->method = self::METHOD_GET;
        $this->options = self::$defaultOptions;
        return $this;
    }
}