<?php

namespace Flare\Http;

/**
 * 
 * @author anthony
 * 
 */
class Session
{
    /**
     * 
     * @var \Flare\Http\Session
     */
    private static $_instance;

    /**
     * 
     * @var string
     */
    const SETTINGS_KEY = '__Flare_session';

    /**
     * 
     * @var boolean
     */
    private $_started = false;
    
    /**
     *
     * @var string
     */
    private $_name;

    /**
     * 
     * @param string $namespace
     * @param boolean $start
     */
    private function __construct($namespace, $start = false)
    {
        $this->_name = $namespace;
        if ($start) {
            $this->start();
        }
    }

    /**
     * 
     * @return \Flare\Http\Session
     */
    public function start()
    {
        if (!$this->_started) {
            session_start();
            $this->_started = true;
        }
        if (!isset($_SESSION[$this->_name])) {
            $_SESSION[$this->_name] = array(
                self::SETTINGS_KEY => array()
            );
        }
        return $this;
    }

    /**
     * 
     * @param string $namespace
     * @param boolean $start
     * @return \Flare\Http\Session
     */
    public static function create($namespace, $start = false)
    {
        if (!self::$_instance) {
            self::$_instance = new self($namespace, $start);
        }
        return self::$_instance;
    }

    /**
     * 
     * @return string
     */
    public function getId()
    {
        return session_id();
    }

    /**
     * 
     * @return string
     */
    public function getNamespace()
    {
        return $this->_name;
    }

    /**
     * 
     * @param string $key
     * @return mixed
     */
    public function __get($key)
    {
        if (!$this->_started) {
            show_error("Session must be started first");
        }
        if (!isset($_SESSION[$this->_name][$key]) || $key === self::SETTINGS_KEY) {
            return null;
        } elseif (isset($_SESSION[$this->_name][self::SETTINGS_KEY][$key])
            && ((time() - $_SESSION[$this->_name][self::SETTINGS_KEY][$key]['create_time'])
                > $_SESSION[$this->_name][self::SETTINGS_KEY][$key]['expiration']))
        {
            unset($_SESSION[$this->_name][$key], $_SESSION[$this->_name][self::SETTINGS_KEY][$key]);
            return null;
        }
        return $_SESSION[$this->_name][$key];
    }

    /**
     * 
     * @param string $key
     * @param mixed $value
     * @return mixed
     */
    public function __set($key, $value)
    {
        if (!$this->_started) {
            show_error("Session must be started first");
        } elseif (strpos($key, '__') === 0) {
            show_error("Key must not have '__' ( underscore )");
        }
        $_SESSION[$this->_name][$key] = $value;
    }

    /**
     * 
     * @param string $key
     * @param mixed $value
     * @param int $expiration
     * @return \Flare\Http\Session
     */
    public function set($key, $value, $expiration = 0)
    {
        $this->__set($key, $value);
        if ($expiration) {
            $this->setExpiration($key, $expiration);
        }
        return $this;
    }

    /**
     * 
     * @param string $key
     * @param int $seconds
     * @param int|string $now
     * @return \Flare\Http\Session
     */
    public function setExpiration($key, $seconds = 1800, $now = null)
    {
        if (!$now) {
            $now = time();
        } elseif (is_string($now)) {
            $now = strtotime($now);
        }
        $_SESSION[$this->_name][self::SETTINGS_KEY][$key] = array(
            'expiration' => $seconds,
            'create_time' => $now
        );
        return $this;
    }

    /**
     * 
     * @param string $key
     * @return int
     */
    public function getExpiration($key)
    {
        if (!isset($_SESSION[$this->_name][self::SETTINGS_KEY][$key]['expiration'])) {
            return null;
        }
        return (int) $_SESSION[$this->_name][self::SETTINGS_KEY][$key]['expiration'];
    }

    /**
     * 
     * @param string $key
     * @return mixed
     */
    public function get($key)
    {
        return $this->__get($key);
    }

    /**
     * 
     * @return array
     */
    public function all()
    {
        $session = $_SESSION[$this->_name];
        unset($session[self::SETTINGS_KEY]);
        foreach ($session as $key => $value) {
            $value = $this->__get($key);
            if ($value === null) {
                unset($session[$key]);
            }
        }
        return $session;
    }

    /**
     * 
     * @param string $key
     * @return boolean
     */
    public function has($key)
    {
        return $this->__isset($key);
    }

    /**
     * 
     * @param string $key
     * @return void
     */
    public function __isset($key)
    {
        if (!$this->_started) {
            show_error("Session must be started first");
        }

        if ($this->__get($key) !== null) {
            return true;
        }
        return false;
    }

    /**
     * 
     * @param string $key
     * @return void
     */
    public function __unset($key)
    {
        if (!$this->_started) {
            show_error("Session must be started first");
        }
        unset(
            $_SESSION[$this->_name][$key],
            $_SESSION[$this->_name][self::SETTINGS_KEY][$key]
        );
    }

    /**
     * 
     * @param string $key
     * @return void
     */
    public function remove($key)
    {
        $this->__unset($key);
    }

    /**
     * 
     * @return \Flare\Http\Session
     */
    public function destroy()
    {
        if (!$this->_started) {
            show_error("Session must be started first");
        }
        session_destroy();
        session_regenerate_id();
        $this->_started = false;
        return $this;
    }

    /**
     * 
     * @return \Flare\Http\Session
     */
    public function resetId()
    {
        if (!$this->_started) {
            show_error("Session must be started first");
        }
        session_regenerate_id();
        return $this;
    }

    /**
     * 
     * @return \Flare\Http\Session
     */
    public function clear()
    {
        if (!$this->_started) {
            show_error("Session must be started first");
        }
        unset($_SESSION[$this->_name]);
        return $this;
    }

    /**
     * 
     * @param string $key
     * @return string|int
     */
    public function flash($key)
    {
        $val = $this->__get($key);
        if ($val !== null) {
            unset(
                $_SESSION[$this->_name][$key],
                $_SESSION[$this->_name][self::SETTINGS_KEY][$key]
            );
        }
        return $val;
    }
}