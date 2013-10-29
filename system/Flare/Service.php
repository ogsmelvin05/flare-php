<?php

namespace Flare;

use Flare\Flare as F;

/**
 * 
 * @author anthony
 * 
 */
abstract class Service
{
    /**
     * 
     * @var array
     */
    private static $services = array();

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
     * @return \Flare\Service
     */
    public static function instance(array $params = array())
    {
        $key = get_called_class();
        if (!isset(self::$services[$key])) {
            if (!$params) {
                $class = basename(str_replace("\\", '/', $key));
                if (F::$config->has('services.'.$class)) {
                    $params = F::$config->get('services.'.$class);
                }
            }
            self::$services[$key] = new static($params);
        }
        return self::$services[$key];
    }

    /**
     * 
     * @param array $param
     * @return void
     */
    abstract protected function init(array $params);
}