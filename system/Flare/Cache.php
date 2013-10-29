<?php

namespace Flare;

use Flare\Flare as F;

/**
 * 
 * @author anthony
 * 
 */
abstract class Cache
{
    /**
     * 
     * @var array
     */
    private static $cache_engines = array();

    /**
     * 
     * @param array $params
     */
    public function __construct(array $params)
    {
        $this->init($params);
    }

    /**
     * 
     * @param array $params
     * @return void
     */
    abstract protected function init(array $params);

    /**
     * 
     * @param array $params
     * @return \Flare\Cache
     */
    public static function instance(array $params = array())
    {
        $key = get_called_class();
        if (!isset(self::$cache_engines[$key])) {
            if (!$params) {
                $class = basename(str_replace("\\", '/', $key));
                if (F::$config->has('cache_engines.'.$class)) {
                    $params = F::$config->get('cache_engines.'.$class);
                }
            }
            self::$cache_engines[$key] = new static($params);
        }
        return self::$cache_engines[$key];
    }

    /**
     * 
     * @param string $key
     * @return mixed
     */
    abstract public function get($key);

    /**
     * 
     * @param string $key
     * @param mixed $value
     * @param int $expiration
     * @return \Flare\Cache
     */
    abstract public function set($key, $value, $expiration = 0);
}