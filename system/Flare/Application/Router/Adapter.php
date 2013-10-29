<?php

namespace Flare\Application\Router;

use Flare\Application\Router\Route\Action;
use Flare\Application\Router\Route;
use Flare\Flare as F;

/**
 * 
 * @author anthony
 * 
 */
abstract class Adapter
{
    /**
     * 
     * @var array
     */
    protected $_routes = array();

    /**
     * 
     * @var array
     */
    protected $_routeModules;

    /**
     * 
     * @param array $modules
     * @return \Flare\Application\Router\Adapter
     */
    public function setRoutingModules(array $modules)
    {
        $this->_routeModules = $modules;
        return $this;
    }

    /**
     * 
     * @return array
     */
    public function getRoutingModules()
    {
        return $this->_routeModules;
    }

    /**
     * 
     * @return \Flare\Application\Router\Route
     */
    abstract public function getRoute();

    /**
     * 
     * @param string $module
     * @param string $controller
     * @param string $action
     * @return \Flare\Application\Router\Route
     */
    protected function _route($module, $controller, $action = null)
    {
        F::$request->setModule($module)
            ->setController($controller);
        if ($action) {
            F::$request->setAction($action);
        }
        
        $path = F::getApp()->getModulesDirectory()
            .F::$request->getModule()
            .'/'
            .F::getApp()->getControllersDirectoryName()
            .strtolower(urldecode(F::$request->getController()))
            .'.php';
        if (!file_exists($path)) {
            return null;
        }

        require_once F::getApp()->getModulesDirectory().F::$request->getModule().'/bootstrap.php';
        require_once $path;
        
        $controller = ucwords(F::$request->getModule())."\\Controllers\\".F::$request->getControllerClassName();
        $route = new Route();
        $route->setModule(F::$request->getModule());
        $route->setController(new $controller());
        if (F::$request->getAction()) {
            $route->setAction(new Action($route->getController(), F::$request->getActionMethodName()));
        }
        
        return $route;
    }

    /**
     * 
     * @return string|null
     */
    protected function _getMatchedCustomRoute(&$customRouteParams = array())
    {
        $route = null;
        $uri = trim((string) F::$uri, '/');
        if (isset($this->_routes[$uri])) {
            $route = $this->_routes[$uri];
        } else {
            foreach ($this->_routes as $key => $class) {
                if (!preg_match("#^{$key}$#", $uri, $matches)) {
                    continue;
                }
                if (strpos($class, '$') !== false) {
                    unset($matches[0]);
                    foreach ($matches as $k => $m) {
                        $class = str_replace('$'.$k, $m, $class);
                    }
                }
                $route = $class;
                break;
            }
        }

        if ($route) {
            $route = explode('(', $route, 2);
            $customRouteParams = isset($route[1]) ? explode(',', rtrim($route[1], ')')) : array();
            $route = isset($route[0]) ? explode('.', $route[0], 4) : array();
            if (!$route) {
                return null;
            }
            $count = count($route);
            if ($count >= 3) {
                if (isset($route[3])) {
                    $route[1] = $route[1].'/'.$route[2];
                    $route[2] = $route[3] ? $route[3] : F::$config->get('router.default_action');
                }
            } elseif ($count == 2) {
                $route[2] = $route[1];
                $route[1] = $route[0];
                $route[0] = F::$config->get('router.default_module');
            } elseif ($count == 1) {
                $route[2] = $route[0];
                $route[1] = F::$config->get('router.default_controller');
                $route[0] = F::$config->get('router.default_module');
            }
        }
        return $route;
    }

    /**
     * 
     * @return string
     */
    public function getConfigModule()
    {
        $customRoute = $this->_getMatchedCustomRoute();
        $module = F::$uri->getSegment(1);

        if ($customRoute) {
            list($module) = $customRoute;
        } elseif (!in_array($module, $this->_routeModules)) {
            $module = F::$config->get('router.default_module');
        }
        return $module;
    }

    /**
     * 
     * @param string $class
     * @return \Flare\Application\Router\Route
     */
    public function getErrorRoute($class)
    {
        $module = $controller = $action = null;
        $class = explode('.', $class, 4);
        $count = count($class);
        if ($count >= 3) {
            list($module, $controller, $action) = $class;
            if (isset($class[3])) {
                $controller = $controller.'/'.$action;
                $action = $class[3] ? $class[3] : F::$config->get('router.default_action');
            }
        } elseif ($count == 2) {
            $module = F::$config->get('router.default_module');
            $controller = $class[0];
            $action = $class[1];
        } elseif ($count == 1) {
            $module = F::$config->get('router.default_module');
            $controller = F::$config->get('router.default_controller');
            $action = $class[0];
        }
        unset($count, $class);
        return $this->_route($module, $controller, $action);
    }
}