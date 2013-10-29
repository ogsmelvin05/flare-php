<?php

namespace Flare\Application;

/**
 *
 * @author anthony
 *
 */
class Config
{
    /**
     * 
     * @var string
     */
    const EXTENSION_NAME = 'php';

    /**
     * 
     * @var string
     */
    private static $_constantsFile = 'constants';

    /**
     * 
     * @var string
     */
    private static $_mainConfigFile = 'config';

    /**
     * 
     * @var array
     */
    private $_defaultKeyNames = array(
        'session' => false,
        'cookie' => false,
        'layout' => false,
        'router' => false,
        'autoload' => false,
        'database' => false,
        'nosql' => false,
        'services' => false,
        'cache_engines' => false,
        'mail' => false
    );

    /**
     * 
     * @var array
     */
    private $_loadedConfigNames = array();

    /**
     * 
     * @var array
     */
    private static $_notAllowedInModuleConfig = array(
        'router.default_module', 'modules'
    );

    /**
     * 
     * @var string
     */
    private $_sourceDir;

    /**
     *
     * @var array
     */
    private $_config;

    /**
     *
     * @param array $config
     * @param string $sourceDir
     */
    private function __construct(array $config, $sourceDir)
    {
        $this->_config = $config;
        foreach ($this->_defaultKeyNames as $name => &$loaded) {
            if (file_exists($sourceDir.$name.'.'.self::EXTENSION_NAME)) {
                $this->_addToConfig($name, (array) require $sourceDir.$name.'.'.self::EXTENSION_NAME);
                $loaded = true;
            }
        }
        if (!isset($this->_config['allow_override'])) {
            $this->_config['allow_override'] = false;
        }

        $this->_sourceDir = $sourceDir;
    }

    /**
     * 
     * @param string $name
     * @param array|string $data
     * @return void
     */
    private function _addToConfig($name, $data)
    {
        foreach ($data as $key => $confValue) { 
            $this->_config[$name.'.'.$key] = $confValue;
        }
    }

    /**
     * 
     * @return string
     */
    public function getSourceDirectory()
    {
        return $this->_sourceDir;
    }

    /**
     *
     * @param string $config_dir
     * @param boolean $requireMainConfig
     * @return \Flare\Application\Config
     */
    public static function load($config_dir, $requireMainConfig = true)
    {
        if (!is_dir($config_dir)) {
            return null;
        }

        $config_dir = rtrim($config_dir, '/').'/';
        if (file_exists($config_dir.self::$_constantsFile.'.'.self::EXTENSION_NAME)) {
            require_once $config_dir.self::$_constantsFile.'.'.self::EXTENSION_NAME;
        }

        $content = array();
        if (file_exists($config_dir.self::$_mainConfigFile.'.'.self::EXTENSION_NAME)) {
            $content = (array) require_once $config_dir.self::$_mainConfigFile.'.'.self::EXTENSION_NAME;
        } elseif ($requireMainConfig) {
            die("'{$config_dir}config.php' file doesn't exists");
        }

        return new self($content, $config_dir);
    }

    /**
     * 
     * @param string $name
     * @return \Flare\Application\Config
     */
    public function read($name)
    {
        if (isset($this->_defaultKeyNames[$name]) && !$this->_defaultKeyNames[$name]) {
            if (file_exists($this->_sourceDir.$name.'.'.self::EXTENSION_NAME)) {
                $this->_addToConfig($name, (array) require $this->_sourceDir.$name.'.'.self::EXTENSION_NAME);
                $this->_defaultKeyNames[$name] = true;
            }
        } elseif (!in_array($name, $this->_loadedConfigNames)) {
            if (file_exists($this->_sourceDir.$name.'.'.self::EXTENSION_NAME)) {
                $this->_addToConfig($name, (array) require $this->_sourceDir.$name.'.'.self::EXTENSION_NAME);
                $this->_loadedConfigNames[] = $name;
            }
        }
        return $this;
    }

    /**
     * 
     * @param string $key
     * @param mixed $value
     * @return \Flare\Application\Config
     */
    public function set($key, $value)
    {
        if (!empty($this->_config['allow_override'])) {
            $count = substr_count($key, '.');
            if ($count >= 2) {
                $key = explode('.', $key, 3);
                if (!isset($this->_config[$key[0].'.'.$key[1]])) {
                    $this->_config[$key[0].'.'.$key[1]] = array();
                }
                $this->_config[$key[0].'.'.$key[1]][$key[2]] = $value;
            } elseif ($count == 1 || (isset($this->_config[$key]) && !$count)) {
                $this->_config[$key] = $value;
            } elseif (!isset($this->_config[$key]) && !$count) {
                if (is_string($value)) {
                    foreach ($this->_config as $keyConf => $conf) {
                        if (strpos($keyConf, $key) === 0) {
                            unset($this->_config[$keyConf]);
                        }
                    }
                    $this->_config[$key] = $value;
                } elseif (is_array($value)) {
                    foreach ($value as $keyConf => $conf) {
                        $this->_config[$key.'.'.$keyConf] = $conf;
                    }
                }
            }
        }
        return $this;
    }

    /**
     * 
     * @param string $key
     * @return mixed
     */
    public function get($key)
    {
        if (isset($this->_config[$key])) {
            return $this->_config[$key];
        }

        $key = explode('.', $key, 3);
        $count = count($key);
        if ($count == 3) {
            if (isset($this->_config[$key[0].'.'.$key[1]][$key[2]])) {
                return $this->_config[$key[0].'.'.$key[1]][$key[2]];
            }
        } elseif ($count == 1) {
            $config = array();
            $namespacelen = strlen($key[0].'.');
            foreach ($this->_config as $keyConf => $conf) {
                if (strpos($keyConf, $key[0]) === 0) {
                    $config[substr($keyConf, $namespacelen)] = $conf;
                }
            }
            if ($config) return $config;
        }
        return null;
    }

    /**
     * 
     * @param string $key
     * @return \Flare\Application\Config
     */
    public function remove($key)
    {
        if (isset($this->_config[$key])) {
            unset($this->_config[$key]);
        } else {
            $count = substr_count($key, '.');
            if ($count == 2) {
                $key = explode('.', $key, 3);
                if (isset($this->_config[$key[0].'.'.$key[1]][$key[2]])) {
                    unset($this->_config[$key[0].'.'.$key[1]][$key[2]]);
                }
            } elseif ($count == 1 || (isset($this->_config[$key]) && !$count)) {
                unset($this->_config[$key]);
            } elseif (!isset($this->_config[$key]) && !$count) {
                foreach ($this->_config as $keyConf => $conf) {
                    if (strpos($keyConf, $key) === 0) {
                        unset($this->_config[$keyConf]);
                    }
                }
            }
        }
        return $this;
    }

    /**
     * 
     * @param string $key
     * @return boolean
     */
    public function has($key)
    {
        if (isset($this->_config[$key])) {
            return true;
        } elseif (substr_count($key, '.') == 2) {
            $key = explode('.', $key, 3);
            if (isset($this->_config[$key[0].'.'.$key[1]][$key[2]])) {
                return true;
            }
        }
        return false;
    }

    /**
     *
     * @return array
     */
    public function all()
    {
        return $this->_config;
    }

    /**
     * 
     * @param \Flare\Application\Config $new
     * @return \Flare\Application\Config
     */
    public function merge(Config $new)
    {
        foreach ($new->all() as $key => $value) {
            if (in_array($key, self::$_notAllowedInModuleConfig)) {
                continue;
            } elseif (isset($this->_config[$key]) && is_array($value)) {
                $value = array_merge((array) $this->_config[$key], $value);
            }
            $this->_config[$key] = $value;
        }
        return $this;
    }
}