<?php

namespace Flare\Application;

use Flare\Flare as F;

/**
 * 
 * @author anthony
 * 
 */
class Router
{
    /**
     * 
     * @var string
     */
    const DEFAULT_ADAPTER = 'page';

    /**
     * 
     * @var \Flare\Application\Router\Route
     */
    private $_currentRoute;

    /**
     * 
     * @var \Flare\Application\Router\Adapter
     */
    private $_adapter;

    /**
     * 
     * @var string
     */
    private $_adapterName = self::DEFAULT_ADAPTER;

    /**
     * 
     * @param string $adapter
     * @return \Flare\Application\Router
     */
    public function setAdapter($adapter)
    {
        $this->_adapterName = strtolower($adapter);
        $adapter = __CLASS__."\\Adapter\\".ucwords($this->_adapterName);
        $this->_adapter = new $adapter;
        return $this;
    }

    /**
     * 
     * @return \Flare\Application\Router\Adapter
     */
    public function getAdapter()
    {
        if (!$this->_adapter) {
            $this->setAdapter(self::DEFAULT_ADAPTER);
        }
        return $this->_adapter;
    }

    /**
     * 
     * @return string
     */
    public function getAdapterName()
    {
        return $this->_adapterName;
    }

    /**
     * 
     * @param array $modules
     * @return \Flare\Application\Router
     */
    public function setRoutingModules($modules)
    {
        $this->getAdapter()->setRoutingModules($modules);
        return $this;
    }

    /**
     * 
     * @return array
     */
    public function getRoutingModules()
    {
        return $this->getAdapter()->getRoutingModules();
    }

    /**
     * 
     * @param string $class
     * @return \Flare\Application\Router\Route|null
     */
    public function useErrorRoute($class)
    {
        $this->_currentRoute = $this->getAdapter()->getErrorRoute($class);
        return $this->_currentRoute;
    }

    /**
     * 
     * @return \Flare\Application\Router\Route|null
     */
    public function getRoute()
    {
        if ($this->_currentRoute) {
            return $this->_currentRoute;
        }

        $this->_currentRoute = $this->getAdapter()->getRoute();
        return $this->_currentRoute;
    }

    /**
     * 
     * @param int $redirectCode
     * @return void
     */
    public function secure($redirectCode = 301)
    {
        if (!F::$uri->isHttps()) {
            $url = 'https://'.F::$uri->host;
            $url .= F::$uri;
            if (!empty($_SERVER['QUERY_STRING'])) {
                $url .= '?'.$_SERVER['QUERY_STRING'];
            }
            F::$response->setRedirect($url, $redirectCode)->send(false);
        }
    }
}