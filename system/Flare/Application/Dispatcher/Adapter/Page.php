<?php

namespace Flare\Application\Dispatcher\Adapter;

use Flare\Application\Dispatcher\Adapter;
use Flare\View\Response\Html;
use Flare\View\Response;

/**
 * 
 * @author anthony
 * 
 */
class Page extends Adapter
{
    /**
     * 
     * @return boolean
     */
    public function dispatch()
    {
        $view = null;
        $this->_controller->init();
        $this->_controller->predispatch();
        
        if ($this->_controller->router->getRoute()->hasReroute()) {
            $view = call_user_func_array(
                array($this->_controller, '__reroute'), 
                $this->_controller->router->getRoute()->getActionParams()  
            );
        } elseif (!$this->_controller->router->getRoute()->getActionParams()) {
            $view = $this->_controller->{$this->_controller->request->getActionMethodName()}();
        } else {
            $view = call_user_func_array(
                array($this->_controller, $this->_controller->request->getActionMethodName()), 
                $this->_controller->router->getRoute()->getActionParams()
            );
        }

        if (!$this->_controller->response->hasContentType()) {
            if (!($view instanceof Response)) {
                if (!empty($view) && !is_string($view)) {
                    show_error("Action must return a 'View\Response' instance or string");
                } elseif ($this->_controller->config->has('default_content_type')) {
                    $this->_controller->response->setContentType($this->_controller->config->get('default_content_type'));
                }
            } else {
                $this->_controller->response->setContentType($view->getContentType());
            }
        }
        
        $this->_controller->postdispatch();
        if ($this->_controller->cookie->hasNewData()) {
            $this->_controller->response->addCookie(
                $this->_controller->cookie->getNamespace(),
                $this->_controller->cookie->serialize(),
                $this->_controller->cookie->getExpiration()
            );
        }

        if ($view instanceof Html && !$view->disabledLayout()) {
            $module = $this->_controller->request->getModule();
            if (!$view->hasLayout() 
                && $this->_controller->config->has('layout.'.$module)
                && $this->_controller->config->get('layout.'.$module.'.auto'))
            {
                $view->withLayout($this->_controller->config->get('layout.'.$module.'.layout'));
            }
        }
        
        $this->_controller->response->setBody($view)->send();
        $this->_controller->complete();

        return true;
    }
}