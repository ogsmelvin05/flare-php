<?php

namespace Flare;

use Flare\Application\ErrorController;
use Flare\Application\Http\Response;
use Flare\Application\Http\Request;
use Flare\Application\Dispatcher;
use Flare\Application\Config;
use Flare\Application\Router;
use Flare\Db\Sql\Connection;
use Flare\Http\Session;
use Flare\Http\Cookie;
use Flare\Flare as F;
use Flare\Http\Uri;

/**
 * 
 * @author anthony
 * 
 */
class Application
{
    /**
     * 
     * @var string
     */
    private $_controllersDirectory;

    /**
     * 
     * @var string
     */
    private $_viewsDirectory;

    /**
     * 
     * @var string
     */
    private $_modulesDirectory;

    /**
     * 
     * @var string
     */
    private $_layoutsDirectory;

    /**
     * 
     * @var string
     */
    private $_modelsDirectory;

    /**
     * 
     * @var string
     */
    private $_librariesDirectory;

    /**
     * 
     * @var string
     */
    private $_helpersDirectory;

    /**
     * 
     * @var string
     */
    private $_configDirectory;

    /**
     * 
     * @var string
     */
    private $_appDirectory;

    /**
     * 
     * @var string
     */
    private $_sysDirectory;

    /**
     * 
     * @var \Flare\Application\AbstractController
     */
    private $_controller;

    /**
     * 
     * @var boolean
     */
    private $_dispatched = false;
    
    /**
     *
     * @var boolean
     */
    private $_predispatched = false;

    /**
     * 
     * @param string $directory
     * @return \Flare\Application
     */
    public function setControllersDirectoryName($directory)
    {
        $this->_controllersDirectory = rtrim(str_replace("\\", '/', $directory), '/').'/';
        return $this;
    }

    /**
     * 
     * @return string
     */
    public function getBaseDirectory()
    {
        return dirname($this->_appDirectory);
    }

    /**
     * 
     * @return string
     */
    public function getControllersDirectoryName()
    {
        return $this->_controllersDirectory;
    }

    /**
     * 
     * @param string $directory
     * @return \Flare\Application
     */
    public function setViewsDirectoryName($directory)
    {
        $this->_viewsDirectory = rtrim(str_replace("\\", '/', $directory), '/').'/';
        return $this;
    }

    /**
     * 
     * @return string
     */
    public function getViewsDirectoryName()
    {
        return $this->_viewsDirectory;
    }

    /**
     * 
     * @param string $module
     * @return string
     */
    private function _getCurrentModule($module = null)
    {
        if (!$module) {
            if (!F::$router->getRoute()) {
                $this->error(500, 'No route found. Predispatch must be executed first.');
            }
            $module = F::$router->getRoute()->getModule();
        }
        return $module;
    }

    /**
     * 
     * @param string $module
     * @return string
     */
    public function getModuleViewsDirectory($module = null)
    {
        return $this->_modulesDirectory.$this->_getCurrentModule().'/'.$this->_viewsDirectory;
    }

    /**
     * 
     * @param string $module
     * @return string
     */
    public function getModuleHelpersDirectory($module = null)
    {
        return $this->_modulesDirectory.$this->_getCurrentModule().'/'.$this->_helpersDirectory;
    }

    /**
     * 
     * @param string $module
     * @return string
     */
    public function getModuleLayoutsDirectory($module = null)
    {
        return $this->_modulesDirectory.$this->_getCurrentModule().'/'.$this->_layoutsDirectory;
    }

    /**
     * 
     * @param string $directory
     * @return \Flare\Application
     */
    public function setModulesDirectory($directory)
    {
        $this->_modulesDirectory = rtrim(str_replace("\\", '/', $directory), '/').'/';
        return $this;
    }

    /**
     * 
     * @return string
     */
    public function getModulesDirectory()
    {
        return $this->_modulesDirectory;
    }

    /**
     * 
     * @param string $directory
     * @return \Flare\Application
     */
    public function setConfigDirectory($directory)
    {
        $this->_configDirectory = rtrim(str_replace("\\", '/', $directory), '/').'/';
        return $this;
    }

    /**
     * 
     * @return string
     */
    public function getConfigDirectory()
    {
        return $this->_configDirectory;
    }

    /**
     * 
     * @param string $directory
     * @return \Flare\Application
     */
    public function setLayoutsDirectoryName($directory)
    {
        $this->_layoutsDirectory = rtrim(str_replace("\\", '/', $directory), '/').'/';
        return $this;
    }

    /**
     * 
     * @return string
     */
    public function getLayoutsDirectoryName()
    {
        return $this->_layoutsDirectory;
    }

    /**
     * 
     * @param string $directory
     * @return \Flare\Application
     */
    public function setHelpersDirectoryName($directory)
    {
        $this->_helpersDirectory = rtrim(str_replace("\\", '/', $directory), '/').'/';
        return $this;
    }

    /**
     * 
     * @return string
     */
    public function getHelpersDirectoryName()
    {
        return $this->_helpersDirectory;
    }

    /**
     * 
     * @param string $directory
     * @return \Flare\Application
     */
    public function setLibrariesDirectory($directory)
    {
        if (!$this->_librariesDirectory) {
            spl_autoload_register(array($this, 'autoloadLibrary'));
        }
        $this->_librariesDirectory = rtrim(str_replace("\\", '/', $directory), '/').'/';
        return $this;
    }

    /**
     * 
     * @param string $class
     * @return void
     */
    public function autoloadLibrary($class)
    {
        require $this->_librariesDirectory.str_replace("\\", "/", ltrim($class, "\\")).'.php';
    }

    /**
     * 
     * @return string
     */
    public function getLibrariesDirectory()
    {
        return $this->_librariesDirectory;
    }

    /**
     * 
     * @param string $directory
     * @return \Flare\Application
     */
    public function setAppDirectory($directory)
    {
        $path = realpath($directory);
        if (!$path) {
            die("'{$directory}' is not readable");
        }
        $this->_appDirectory = rtrim(str_replace("\\", '/', $path), '/').'/';
        return $this;
    }

    /**
     * 
     * @return string
     */
    public function getAppDirectory()
    {
        return $this->_appDirectory;
    }

    /**
     * 
     * @param string $directory
     * @return \Flare\Application
     */
    public function setSystemDirectory($directory)
    {
        $this->_sysDirectory = rtrim(str_replace("\\", '/', $directory), '/').'/';
        return $this;
    }

    /**
     * 
     * @return string
     */
    public function getSystemDirectory()
    {
        return $this->_sysDirectory;
    }

    /**
     * 
     * @param array $modules
     * @return \Flare\Application
     */
    public function setModules($modules)
    {
        if (!in_array(F::$config->get('router.default_module'), $modules)) {
            $modules[] = F::$config->get('router.default_module');
        }
        F::$router->setRoutingModules($modules);
        return $this;
    }

    /**
     * 
     * @return array
     */
    public function getModules()
    {
        return F::$router->getRoutingModules();
    }

    /**
     * 
     * @param string $directory
     * @return \Flare\Application
     */
    public function setModelsDirectoryName($directory)
    {
        if (!$this->_modelsDirectory) {
            spl_autoload_register(array($this, 'autoloadModel'));
        }
        $this->_modelsDirectory = rtrim(str_replace("\\", '/', $directory), '/').'/';
        return $this;
    }

    /**
     * 
     * @return string
     */
    public function getModelsDirectoryName()
    {
        return $this->_modelsDirectory;
    }

    /**
     * 
     * @param string $class
     * @return void
     */
    public function autoloadModel($class)
    {
        $class = explode("\\", $class);
        if (isset($class[1]) && $class[1] == 'Models') {
            $className = array_pop($class);
            require $this->_modulesDirectory.strtolower(implode('/', $class)).'/'.$className.'.php';
        }
    }

    /**
     * 
     * @return \Flare\Application
     */
    private function _predispatch()
    {
        if (F::$uri->isValid()) {
            $route = F::$router->getRoute();
            if (!$route) {
                $this->error(404);
            } elseif ($route->getController() instanceof ErrorController
                && $route->getController()->response->getStatusCode() === Response::DEFAULT_CODE)
            {
                $route->getController()->response->setStatusCode(404);
            }
        } else {
            $this->error(400);
        }
        
        $this->_controller = F::$router->getRoute()->getController();
        F::$uri->setModuleUrl();
        $this->_predispatched = true;
        return $this;
    }

    /**
     * 
     * @return void
     */
    private function _dispatch()
    {
        if (!$this->_controller) {
            $this->error(500, 'Controller is not initilized');
        }
        
        $dispatcher = new Dispatcher($this->_controller, F::$router->getAdapterName());
        if ($dispatcher->dispatch()) {
            $this->_dispatched = true;
        }

        return $this;
    }

    /**
     * 
     * @param int $code
     * @param string $message
     * @param boolean $skipConfig
     * @return void
     */
    public function error($code, $message = '', $skipConfig = false)
    {
        $html = null;
        F::$router->setAdapter('page');
        if (!$skipConfig && F::$config->get('router.errors')) {

            $route = null;
            if (is_array(F::$config->get('router.errors'))) {
                if (F::$config->has("router.errors.{$code}")) {
                    $route = F::$router->useErrorRoute(F::$config->get("router.errors.{$code}"));
                }
            } else {
                $route = F::$router->useErrorRoute(F::$config->get('router.errors'));
            }
            if (!$route || !($route->getController() instanceof ErrorController) || !$route->getAction()->exists()) {
                $this->error($code, $message, true);
            }

            $route->getController()->setErrorCode($code);
            if ($message) {
                $route->getController()->setErrorMessage($message);
            }
            if ($this->_predispatched) {
                $this->_predispatch();
                if (!$this->_dispatched) {
                    $this->_dispatch()
                        ->shutdown(true);
                }
            }
            return;
            
        } elseif ($message) {
            $html = '<pre>'.$message.'</pre>';
        } elseif (isset(Response::$messages[$code])) {
            $html = '<pre>'.Response::$messages[$code].'</pre>';
        }
        
        F::$response->setStatusCode($code)
            ->setBody($html)
            ->send();
        $this->shutdown(true);
    }

    /**
     * 
     * @return \Flare\Application\AbstractController
     */
    public function getController()
    {
        return $this->_controller;
    }

    /**
     * 
     * @return void
     */
    public function start()
    {
        if ($this->_dispatched) {
            $this->error(500, 'Application is already started');
        } elseif (!$this->_appDirectory) {
            $this->error(500, 'App Directory and System Directory must be set');
        }
        $this->setConfigDirectory($this->_appDirectory.'config')
            ->setModulesDirectory($this->_appDirectory.'modules')
            ->setHelpersDirectoryName('helpers')
            ->setLayoutsDirectoryName('layouts')
            ->setControllersDirectoryName('controllers')
            ->setModelsDirectoryName('models')
            ->setViewsDirectoryName('views')
            ->setLibrariesDirectory($this->_appDirectory.'libraries')
            ->_init()
            ->setModules(F::$config->get('modules'))
            ->_configure()
            ->_predispatch()
            ->_dispatch()
            ->shutdown();
    }

    /**
     * 
     * @param boolean $withExit
     * @return void
     */
    public function shutdown($withExit = false)
    {
        Connection::destroy();
        if ($withExit) {
            exit;
        }
    }

    /**
     *
     * @return \Flare\Application
     */
    private function _init()
    {
        F::$config = Config::load($this->_configDirectory);
        if (!F::$config) {
            die("Config directory doesn't exists or not readable");
        }
        F::$request = new Request();
        F::$response = new Response();
        F::$uri = new Uri();
        F::$router = new Router();
        return $this;
    }

    /**
     * 
     * @return void
     */
    private function _setupCookie()
    {
        if (F::$config->get('cookie.namespace')) {
            if (F::$config->get('cookie.enable_encryption') && !F::$config->get('cookie.encryption_key')) {
                $this->error(500, 'Config[encryption_key] must be set');
            }
            F::$cookie = Cookie::create(
                F::$config->get('cookie.namespace'),
                F::$config->get('cookie.expiration'),
               F::$config->get('cookie.enable_encryption') ? F::$config->get('cookie.encryption_key') : false
            );
        } else {
            $this->error(500, 'Config[cookie][namespace] must be set');
        }
    }

    /**
     * 
     * @return void
     */
    private function _setupRouter()
    {
        if (F::$config->get('router.force_https')) {
            F::$router->secure();
        }
        if (F::$config->get('router.routes') && F::$router->getAdapterName() == Router::DEFAULT_ADAPTER) {
            F::$router->getAdapter()->addRoutes(F::$config->get('router.routes'));
        }
    }

    /**
     * 
     * @return void
     */
    private function _setupSession()
    {
        if (F::$config->get('session.namespace')) {
            F::$session = Session::create(
                F::$config->get('session.namespace'),
                F::$config->get('session.auto_start')
            );
        } else {
            $this->error(500, 'Config[session][namespace] must be set');
        }
    }

    /**
     * 
     * @param string $module
     * @param array $components
     * @return \Flare\Application
     */
    private function _configure($module = null, $components = array())
    {
        if (!$module) {
            $module = $this->_modulesDirectory.F::$router->getAdapter()->getConfigModule().'/config/';
        } else {
            $module = $module.'/config/';
        }
        
        $moduleConf = Config::load($module, false);
        if ($moduleConf) {
            F::$config->merge($moduleConf);
        }
        unset($moduleConf);

        if (F::$config->get('time_limit') !== '') {
            set_time_limit((int) F::$config->get('time_limit'));
        }
        if (F::$config->get('memory_limit') !== '') {
            ini_set('memory_limit', F::$config->get('memory_limit'));
        }
        if (F::$config->get('timezone') !== '') {
            date_default_timezone_set(F::$config->get('timezone'));
        }
        
        if (!$components) {
            $this->_setupRouter();
            $this->_setupSession();
            $this->_setupCookie();
        } else {
            foreach ($components as $component) {
                $this->{'_setup'.$component}();
            }
        }
        
        $this->_compress();
        return $this;
    }
    
    /**
     * 
     * @return void
     */
    private function _compress()
    {
        if (F::$config->get('auto_compress') && !@ini_get('zlib.output_compression')
            && extension_loaded('zlib') && isset($_SERVER['HTTP_ACCEPT_ENCODING']) 
            && strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') !== FALSE)
        {
            if (!ob_start('ob_gzhandler')) {
                $this->error(500, 'output compression failed');
            }
        }
    }
}