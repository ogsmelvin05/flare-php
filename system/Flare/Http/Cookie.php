<?php

namespace Flare\Http;

use Flare\Security\Crypt;
use Flare\Flare as F;

/**
 * 
 * @author anthony
 * 
 */
class Cookie
{
    /**
     * 
     * @var \Flare\Http\Cookie
     */
    private static $_instance;

    /**
     * 
     * @var string
     */
    private $_encryptionKey;

    /**
     * 
     * @var int
     */
    private $_expiry = 0;

    /**
     * 
     * @var string
     */
    private $_namespace;

    /**
     * 
     * @var array
     */
    private $_cookies;

    /**
     * 
     * @var array
     */
    private $_info;

    /**
     * 
     * @var array
     */
    private $_newData = array();

    /**
     * 
     * @param string $name
     * @param int $expiry
     * @param string $encryptionKey
     */
    private function __construct($name, $expiry = 0, $encryptionKey = null)
    {
        $this->_namespace = str_replace('.', '_', $name);
        if ($expiry) {
            $this->_expiry = time() + $expiry;
        }
        if ($encryptionKey) {
            $this->_encryptionKey = $encryptionKey;
        }

        $this->_fetchCookies();
    }

    /**
     * 
     * @return void
     */
    private function _fetchCookies()
    {
        $this->_cookies = array();
        $this->_info = array(
            'client_ip' => F::$request->getClientIp()
        );

        $tmp = isset($_COOKIE[$this->_namespace]) ? $_COOKIE[$this->_namespace] : null;
        if ($tmp) {
            if ($this->_encryptionKey) {
                $tmp = Crypt::decode($tmp, $this->_encryptionKey);
            }
            $tmp = unserialize($tmp);
            if (isset($tmp['client_ip']) && $this->_info['client_ip'] == $tmp['client_ip']) {
                if (isset($tmp['data']) && is_array($tmp['data'])) {
                    $this->_cookies = $tmp['data'];
                }
            }
        }
    }

    /**
     * 
     * @param string $name
     * @param int $expiry
     * @param string $encryptionKey
     * @return \Flare\Http\Cookie
     */
    public static function create($name, $expiry = 0, $encryptionKey = null)
    {
        if (!isset(self::$_instance)) {
            self::$_instance = new self($name, $expiry, $encryptionKey);
        }
        return self::$_instance;
    }

    /**
     * 
     * @param string $name
     * @param string $value
     * @return \Flare\Http\Cookie
     */
    public function set($name, $value)
    {
        $this->_newData[$name] = $value;
        return $this;
    }

    /**
     * 
     * @return string
     */
    public function getNamespace()
    {
        return $this->_namespace;
    }

    /**
     * 
     * @return boolean
     */
    public function exists()
    {
        return isset($_COOKIE[$this->_namespace]);
    }

    /**
     * 
     * @return int
     */
    public function getExpiration()
    {
        return $this->_expiry;
    }

    /**
     * 
     * @param string $name
     * @return boolean
     */
    public function has($name)
    {
        return isset($this->_cookies[$name]) || isset($this->_newData[$name]);
    }

    /**
     * 
     * @param string $name
     * @param boolean $xss
     * @return mixed
     */
    public function get($name, $xss = false)
    {
        $value = null;
        if (isset($this->_newData[$name])) {
            $value = $this->_newData[$name];
        } elseif (isset($this->_cookies[$name])) {
            $value = $this->_cookies[$name];
        }
        return $xss ? Xss::filter($value) : $value;
    }

    /**
     * 
     * @return string|null
     */
    public function serialize()
    {
        $data = serialize(array_merge(array('data' => $this->getData()), $this->_info));
        if ($this->_encryptionKey) {
            $data = Crypt::encode($data, $this->_encryptionKey);
        }
        return $data;
    }

    /**
     * 
     * @return boolean
     */
    public function hasNewData()
    {
        return !empty($this->_newData);
    }

    /**
     * 
     * @return array
     */
    public function getData()
    {
        return array_merge($this->_cookies, $this->_newData);
    }

    /**
     * 
     * @return array
     */
    public function getNewData()
    {
        return $this->_newData;
    }

    /**
     * 
     * @return string
     */
    public function getIp()
    {
        return isset($this->_info['client_ip']) ? $this->_info['client_ip'] : null;
    }

    /**
     * 
     * @return array
     */
    public function getInfo()
    {
        return $this->_info;
    }

    /**
     * 
     * @param string $key
     * @return \Flare\Http\Cookie
     */
    public function remove($key)
    {
        unset($this->_cookies[$key], $this->_newData[$key]);
        return $this;
    }

    /**
     * 
     * @param string $key
     * @return mixed
     */
    public function __get($key)
    {
        return $this->get($key);
    }

    /**
     * 
     * @param string $key
     * @return void
     */
    public function __unset($key)
    {
        $this->remove($key);
    }

    /**
     * 
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function __set($key, $value)
    {
        $this->set($key, $value);
    }

    /**
     * 
     * @param string $key
     * @return boolean
     */
    public function __isset($key)
    {
        return $this->has($key);
    }
}