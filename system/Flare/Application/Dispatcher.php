<?php

namespace Flare\Application;

use Flare\Application\BaseController;

/**
 * 
 * @author anthony
 * 
 */
class Dispatcher
{
    /**
     * 
     * @param \Flare\Application\BaseController $controller
     * @param string $adapter
     */
    public function __construct(BaseController &$controller, $adapter = 'page')
    {
        $adapter = __CLASS__."\\Adapter\\".ucwords(strtolower($adapter));
        if ($controller->config->get('autoload.database')) {
            $controller->setDatabase($controller->config->get('autoload.database'));
        }
        if ($controller->config->get('autoload.helpers')) {
            foreach ($controller->config->get('autoload.helpers') as $helper) {
                $controller->setHelper($helper);
            }
        }
        $this->_adapter = new $adapter($controller);
    }

    /**
     * 
     * @return \Flare\Application\Dispatcher\Adapter
     */
    public function getAdapter()
    {
        return $this->_adapter;
    }

    /**
     * 
     * @return void
     */
    public function dispatch()
    {
        $this->_adapter->dispatch();
    }
}