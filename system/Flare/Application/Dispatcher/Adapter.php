<?php

namespace Flare\Application\Dispatcher;

use Flare\Application\AbstractController;

/**
 * 
 * @author anthony
 * 
 */
abstract class Adapter
{
    /**
     * 
     * @var string
     */
    const FLARE_JS_HEADER = 'X-Flare-JS';

    /**
     * 
     * @var \Flare\Application\AbstractController
     */
    protected $_controller;

    /**
     * 
     * @param \Flare\Application\AbstractController $controller
     */
    public function __construct(AbstractController &$controller)
    {
        $this->_controller = & $controller;
    }

    /**
     * 
     * @return boolean
     */
    abstract public function dispatch();
}