<?php

namespace Flare\Application\Router\Adapter;

use Flare\Application\Router\Adapter;
use Flare\Application\Router\Route;
use Flare\Flare as F;

/**
 * 
 * @author anthony
 * 
 */
class Page extends Adapter
{
    /**
     * 
     * @param string $url
     * @param string $method
     * @return \Flare\Application\Router
     */
    public function addRoute($url, $method)
    {
        $this->_routes[trim($url, '/')] = trim($method);
        return $this;
    }

    /**
     * 
     * @param array $routes
     * @return \Flare\Application\Router
     */
    public function addRoutes(array $routes)
    {
        foreach ($routes as $key => $route) {
            $this->addRoute($key, $route);
        }
        return $this;
    }

    /**
     * 
     * @return \Flare\Application\Router
     */
    public function clearRoutesList()
    {
        $this->_routes = array();
        return $this;
    }

    /**
     * 
     * @return array
     */
    public function getRoutesList()
    {
        return $this->_routes;
    }

    /**
     * 
     * @return string
     */
    private function _removeUriSuffix($segment)
    {
        return basename($segment, '.'.F::$config->get('router.url_suffix'));
    }

    /**
     * 
     * @param \Flare\Application\Router\Route $route
     * @param string $action
     * @return boolean
     */
    private function _validSuffix($route, $action)
    {
        if (F::$config->get('router.url_suffix')) {
            $matchActionSuffix = (pathinfo($action, PATHINFO_EXTENSION) === F::$config->get('router.url_suffix'));
            $matchUriSuffix = (F::$uri->suffix === F::$config->get('router.url_suffix'));
            if ((!$route->getActionParams() && $action !== F::$config->get('router.default_action') && !$matchActionSuffix)
                || ($route->getActionParams() && ($matchActionSuffix || !$matchUriSuffix))) {
                return false;
            }
        }
        return true;
    }

    /**
     * 
     * @return \Flare\Application\Router\Route|null
     */
    public function getRoute()
    {
        $customRoute = $this->_getMatchedCustomRoute($customRouteParams);
        $module = F::$uri->getSegment(1);
        $controller = F::$uri->getSegment(2);
        $action = F::$uri->getSegment(3);

        if ($customRoute) {
            list($module, $controller, $action) = $customRoute;
        } elseif ($module === null) {
            $module = F::$config->get('router.default_module');
            $action = F::$config->get('router.default_action');
            $controller = F::$config->get('router.default_controller');
        } elseif (!in_array($module, $this->_routeModules)) {
            $action = $controller;
            $controller = $module;
            $module = F::$config->get('router.default_module');
        }

        $controller = $controller === null ? F::$config->get('router.default_controller') : $controller;
        $action = $action === null ? F::$config->get('router.default_action') : $action;

        if (!$customRoute 
            && is_dir(F::getApp()->getModulesDirectory().$module
                .'/'.F::getApp()->getControllersDirectoryName().$controller))
        {
            $tmpController = null;
            $segmentActionIndex = 3;
            $firstSegment = F::$uri->getSegment(1);
            if ($firstSegment && in_array($firstSegment, $this->_routeModules)) {
                $tmpController = F::$uri->getSegment($segmentActionIndex);
                $segmentActionIndex++;
            } else {
                $tmpController = F::$uri->getSegment($segmentActionIndex - 1);
            }
            if (!$tmpController) {
                $tmpController = F::$config->get('router.default_controller');
            }
            $controller = $controller.'/'.$tmpController;
            $action = F::$uri->getSegment($segmentActionIndex);
            if (!$action) {
                $action = F::$config->get('router.default_action');
            }
            unset($segmentActionIndex, $firstSegment, $tmpController);
        }

        $route = $this->_route($module, $controller, $this->_removeUriSuffix($action));
        if ($route) {
            if (!$customRoute) {
                if (!$route->hasReroute()) {
                    $this->_setActionParams($route, $validUriForParams);
                    if (!$validUriForParams || !$this->_validSuffix($route, $action)) {
                        return null;
                    }
                } elseif (!$this->_validSuffix($route, $action)) {
                    return null;
                } else {
                    $this->_setRerouteParams($route);
                }
            } elseif (!$route->getAction()->exists()) {
                return null;
            } else {
                $route->setActionParams($customRouteParams, F::$config->get('action_xss_filter'));
            }
        }

        return $route;
    }

    /**
     * 
     * @param \Flare\Application\Router\Route $route
     * @return void
     */
    private function _setRerouteParams(Route &$route)
    {
        $route->setActionParams(
            array(F::$request->getAction(), F::$uri->getSegments()),
            F::$config->get('action_xss_filter')
        );
    }

    /**
     * 
     * @param \Flare\Application\Router\Route $route
     * @param boolean $validUriForParams
     * @return void
     */
    private function _setActionParams(Route &$route, &$validUriForParams = null)
    {
        $actionParams = array();
        if (!$route->getAction()->exists()) {
            $validUriForParams = false;
            return;
        } else {
            $segmentCount = F::$uri->getSegmentCount();
            $firstSegment = F::$uri->getSegment(1);
            if (!$firstSegment) $firstSegment = F::$config->get('router.default_module');
            $params = $route->getAction()->getParameters();
            $indexStart = 3;
            if (F::$request->hasSubmodule()) {
                $indexStart++;
            }
            if ($params) {
                if ($firstSegment) {
                    if (in_array($firstSegment, $this->_routeModules)) {
                        $indexStart++;
                    }
                } else {
                    $validUriForParams = false;
                    return;
                }

                if (!$params[0]->isOptional() && $segmentCount < $indexStart) {
                    $validUriForParams = false;
                    return;
                }

                $i = $indexStart;
                foreach ($params as $param) {
                    if ($i <= $segmentCount) {
                        if ($segmentValue = F::$uri->getSegment($i++)) {
                            $actionParams[] = $segmentValue;
                        }
                    }
                }

                if ($actionParams && F::$config->get('router.url_suffix')) {
                    $lastIndex = count($actionParams) - 1;
                    $actionParams[$lastIndex] = $this->_removeUriSuffix($actionParams[$lastIndex]);
                }
                
                $segmentParamsCount = ($segmentCount - $indexStart) + 1;
                $segmentParamsCount = $segmentParamsCount < 0 ? 1 : $segmentParamsCount;
                if ($segmentParamsCount > $route->getAction()->getNumberOfParameters()
                    || $segmentParamsCount < $route->getAction()->getNumberOfRequiredParameters())
                {
                    $validUriForParams = false;
                    return;
                }
            } else {
                if ($firstSegment && in_array($firstSegment, $this->_routeModules)) {
                    $indexStart++;
                }
                if ($segmentCount >= $indexStart) {
                    $validUriForParams = false;
                    return;
                }
            }
            unset($params, $indexStart, $segmentCount, $firstSegment);
        }

        $validUriForParams = true;
        $route->setActionParams($actionParams, F::$config->get('action_xss_filter'));
    }
}