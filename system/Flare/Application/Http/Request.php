<?php

namespace Flare\Application\Http;

use Flare\Http\Request as ParentRequest;

/**
 * 
 * @author anthony
 * 
 */
class Request extends ParentRequest
{
    /**
     *
     * @var string
     */
    private $_module;

    /**
     *
     * @var string
     */
    private $_controller;

    /**
     * 
     * @var string
     */
    private $_controllerClassName;

    /**
     *
     * @var string
     */
    private $_action;

    /**
     * 
     * @var string
     */
    private $_submodule;

    /**
     *
     * @param string $module
     * @return \Flare\Application\Http\Request
     */
    public function setModule($module)
    {
        $this->_module = $module;
        return $this;
    }

    /**
     * 
     * @return boolean
     */
    public function hasSubmodule()
    {
        return $this->_submodule ? true : false;
    }

    /**
     * 
     * @return string
     */
    public function getSubmodule()
    {
        return $this->_submodule;
    }

    /**
     *
     * @param string $controller
     * @return \Flare\Application\Http\Request
     */
    public function setController($controller)
    {
        $this->_controller = strtolower(urldecode($controller));
        $controller = explode('/', $this->_controller, 2);
        if (count($controller) === 2) {
            $this->_submodule = $controller[0];
            $controller[0] = ucwords($controller[0]);
            $controller[1] = str_replace(' ', '_', ucwords(str_replace('_', ' ', $controller[1])));
            $this->_controllerClassName = $controller[0]."\\".$controller[1];
        } else {
            $this->_controllerClassName = str_replace(' ', '_', ucwords(str_replace('_', ' ', $this->_controller)));
        }
        return $this;
    }

    /**
     * 
     * @return string
     */
    public function getControllerClassName()
    {
        return $this->_controllerClassName;
    }

    /**
     *
     * @param string $action
     * @return \Flare\Application\Http\Request
     */
    public function setAction($action)
    {
        $this->_action = urldecode($action);
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
     * @return string
     */
    public function getController()
    {
        return $this->_controller;
    }

    /**
     *
     * @return string
     */
    public function getAction()
    {
        return $this->_action;
    }

    /**
     * 
     * @return string
     */
    public function getActionMethodName()
    {
        return $this->_action;
    }
}