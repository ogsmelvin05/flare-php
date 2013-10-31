<?php

namespace Flare\Application\Dispatcher;

use Flare\Application\BaseController;

/**
 * 
 * @author anthony
 * 
 */
abstract class Adapter
{
    /**
     * 
     * @var \Flare\Application\BaseController
     */
    protected $_controller;

    /**
     * 
     * @param \Flare\Application\BaseController $controller
     */
    public function __construct(BaseController &$controller)
    {
        $this->_controller = & $controller;
    }

    /**
     * 
     * @return boolean
     */
    abstract public function dispatch();
}