<?php

namespace Flare\Http;

use Flare\Http\AbstractRequest;
use Flare\Security\Xss;
use Flare\Flare as F;

/**
 *
 * @author anthony
 *
 */
class Request extends AbstractRequest
{
    /**
     * 
     * @param string|array $value
     * @param boolean|null $xss
     * @return string|array
     */
    protected function _filter($value, $xss = null)
    {
        if ($value && (($xss === null && F::$config->get('request_xss_filter')) || $xss === true)) {
            $value = Xss::filter($value);
        }
        return $value;
    }

    /**
     * 
     * @param boolean $xss
     * @return array
     */
    public function allPost($xss = null)
    {
        return $this->post(null, $xss);
    }

    /**
     * 
     * @param boolean $xss
     * @return array
     */
    public function allGet($xss = null)
    {
        return $this->get(null, $xss);
    }

    /**
     * 
     * @param boolean $xss
     * @return array
     */
    public function allCookies($xss = null)
    {
        return $this->cookie(null, $xss);
    }

    /**
     * 
     * @param boolean $xss
     * @return array
     */
    public function allParams($xss = null)
    {
        return $this->param(null, $xss);
    }

    /**
     * 
     * @param boolean $xss
     * @return array
     */
    public function allServer($xss = null)
    {
        return $this->server(null, $xss);
    }

    /**
     * 
     * @param boolean $xss
     * @return array
     */
    public function allPut($xss = null)
    {
        return $this->put(null, $xss);
    }

    /**
     * 
     * @param string|null $key
     * @param boolean|null $xss
     * @return mixed
     */
    public function post($key = null, $xss = null)
    {
        $value = null;
        if ($key === null) {
            if (!empty($_POST)) $value = $_POST;
        } elseif (isset($_POST[$key])) {
            $value = $_POST[$key];
        }
        return $this->_filter($value, $xss);
    }

    /**
     * 
     * @param string|null $key
     * @param boolean|null $xss
     * @return mixed
     */
    public function param($key = null, $xss = null)
    {
        $value = null;
        if ($key === null) {
            if (!empty($_REQUEST)) $value = $_REQUEST;
        } elseif (isset($_REQUEST[$key])) {
            $value = $_REQUEST[$key];
        }
        return $this->_filter($value, $xss);
    }

    /**
     * 
     * @param string|null $key
     * @param boolean|null $xss
     * @return mixed
     */
    public function get($key = null, $xss = null)
    {
        $value = null;
        if ($key === null) {
            if (!empty($_GET)) $value = $_GET;
        } elseif (isset($_GET[$key])) {
            $value = $_GET[$key];
        }
        return $this->_filter($value, $xss);
    }

    /**
     * 
     * @param string|null $key
     * @param boolean|null $xss
     * @return mixed
     */
    public function cookie($key = null, $xss = null)
    {
        $value = null;
        if ($key === null) {
            if (!empty($_COOKIE)) $value = $_COOKIE;
        } elseif (isset($_COOKIE[$key])) {
            $value = $_COOKIE[$key];
        }
        return $this->_filter($value, $xss);
    }

    /**
     *
     * @param string|null $key
     * @param boolean|null $xss
     * @return string
     */
    public function server($key = null, $xss = null)
    {
        $value = null;
        if ($key !== null) {
            $key = strtoupper(str_replace('-', '_', $key));
            if (isset($_SERVER[$key])) {
                $value = $_SERVER[$key];
            }
        } else {
            if (!empty($_SERVER)) $value = $_SERVER;
        }
        return $this->_filter($value, $xss);
    }

    /**
     * 
     * @param string|null $key
     * @param boolean|null $xss
     * @return mixed
     */
    public function put($key = null, $xss = null)
    {
        $value = null;
        if ($key === null) {
            if (!empty($_PUT)) $value = $_PUT;
        } elseif (isset($_PUT[$key])) {
            $value = $_PUT[$key];
        }
        return $this->_filter($value, $xss);
    }

    /**
     * 
     * @return string
     */
    public function getMethod()
    {
        return $this->server('REQUEST_METHOD');
    }

    /**
     *
     * @return boolean
     */
    public function isPost()
    {
        return ($this->server('REQUEST_METHOD') === self::METHOD_POST);
    }

    /**
     *
     * @return boolean
     */
    public function isGet()
    {
        return ($this->server('REQUEST_METHOD') === self::METHOD_GET);
    }

    /**
     * 
     * @return boolean
     */
    public function isPut()
    {
        return ($this->server('REQUEST_METHOD') === self::METHOD_PUT);
    }

    /**
     * 
     * @return boolean
     */
    public function isDelete()
    {
        return ($this->server('REQUEST_METHOD') === self::METHOD_DELETE);
    }

    /**
     *
     * @return boolean
     */
    public function isAjax()
    {
        return (strtoupper($this->server('HTTP_X_REQUESTED_WITH')) === 'XMLHTTPREQUEST');
    }

    /**
     * 
     * @param boolean $checkProxy
     * @return string
     */
    public function getClientIp($checkProxy = true)
    {
        $ip = null;
        if ($checkProxy && $this->server('HTTP_CLIENT_IP') != null) {
            $ip = $this->server('HTTP_CLIENT_IP');
        } elseif ($checkProxy && $this->server('HTTP_X_FORWARDED_FOR') != null) {
            $ip = $this->server('HTTP_X_FORWARDED_FOR');
        } elseif ($checkProxy && $this->server('HTTP_X_CLUSTER_CLIENT_IP')) {
            $ip = $this->server('HTTP_X_CLUSTER_CLIENT_IP');
        } else {
            $ip = $this->server('REMOTE_ADDR');
        }
        return $ip;
    }

    /**
     * 
     * @return boolean
     */
    public function isFlash()
    {
        $header = '';
        if ($this->server('HTTP_USER_AGENT')) {
            $header = strtolower($this->server('HTTP_USER_AGENT'));
        } elseif (function_exists('apache_request_headers')) {
            $headers = apache_request_headers();
            if (isset($headers['HTTP_USER_AGENT'])) {
                $header = strtolower($headers['HTTP_USER_AGENT']);
            }
        }
        return (strstr($header, ' flash')) ? true : false;
    }
}