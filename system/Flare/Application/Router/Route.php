<?php

namespace Flare\Application\Router;

use Flare\Application\Router\Route\Action;
use Flare\Application\BaseController;
use Flare\Security\Xss;
use Flare\Security\Uri;

/**
 * 
 * @author anthony
 * 
 */
class Route
{
    /**
     * 
     * @var \Flare\Application\BaseController
     */
    private $_controller;

    /**
     * 
     * @var string
     */
    private $_module;

    /**
     * 
     * @var string
     */
    private $_action;

    /**
     * 
     * @var array
     */
    private $_params = array();

    /**
     * 
     * @return boolean
     */
    private $_hasReroute = false;

    /**
     * 
     * @return \Flare\Application\BaseController
     */
    public function getController()
    {
        return $this->_controller;
    }

    /**
     * 
     * @param \Flare\Application\BaseController
     * @return \Flare\Application\Router\Route
     */
    public function setController(BaseController $controller)
    {
        $this->_controller = $controller;
        $this->_hasReroute = method_exists($controller, '__reroute');
        return $this;
    }

    /**
     * 
     * @return string
     */
    public function getModule()
    {
        return $this->_module;
    }

    /**
     * 
     * @param string $module
     * @return \Flare\Application\Router\Route
     */
    public function setModule($module)
    {
        $this->_module = $module;
        return $this;
    }

    /**
     * 
     * @return \Flare\Application\Router\Route\Action
     */
    public function getAction()
    {
        return $this->_action;
    }

    /**
     * 
     * @param \Flare\Application\Router\Route\Action $action
     * @return \Flare\Application\Router\Route
     */
    public function setAction(Action $action)
    {
        $this->_action = $action;
        return $this;
    }

    /**
     * 
     * @param array $params
     * @param boolean $xss_filter
     * @return \Flare\Application\Router\Route
     */
    public function setActionParams(array $params, $xss_filter = false)
    {
        $params = Uri::decode($params);
        if ($xss_filter === true) {
            $params = Xss::filter($params);
        }
        $this->_params = $params;
        return $this;
    }

    /**
     * 
     * @return array
     */
    public function getActionParams()
    {
        return $this->_params;
    }

    /**
     * 
     * @return boolean
     */
    public function hasReroute()
    {
        return $this->_hasReroute;
    }
}