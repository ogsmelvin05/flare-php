<?php

namespace Flare\Application;

use Flare\View\Response\Html;
use Flare\View\Response\Json;
use Flare\View\Response\Xml;
use Flare\Db\Sql\Connection;
use Flare\Application\Data;
use Flare\Util\Collection;
use Flare\Flare as F;
use Flare\Http\File;

/**
 * 
 * @author anthony
 * 
 */
abstract class AbstractController
{
    /**
     * 
     * @var \Flare\Http\Session
     */
    public $session;

    /**
     * 
     * @var \Flare\Application\Config
     */
    public $config;

    /**
     * 
     * @var \Flare\Application\Http\Request
     */
    public $request;

    /**
     * 
     * @var \Flare\Application\Http\Response
     */
    public $response;

    /**
     * 
     * @var \Flare\Application\Router
     */
    public $router;

    /**
     * 
     * @var \Flare\Http\Uri
     */
    public $uri;

    /**
     * 
     * @var \Flare\Http\Cookie
     */
    public $cookie;

    /**
     * 
     * @var \Flare\Db\Sql\Driver
     */
    protected $db;
    
    public function __construct()
    {
        $this->uri = & F::$uri;
        $this->cookie = & F::$cookie;
        $this->config = & F::$config;
        $this->router = & F::$router;
        $this->session = & F::$session;
        $this->request = & F::$request;
        $this->response = & F::$response;
        $this->data = new Data();
    }

    /**
     * 
     * @return \Flare\Application\AbstractController
     */
    public function disableCache()
    {
        $this->response->setHeader('Expires: Mon, 26 Jul 1997 05:00:00 GMT')
            ->setHeader('Last-Modified: '.gmdate("D, d M Y H:i:s").' GMT')
            ->setHeader('Cache-Control: no-store, no-cache, must-revalidate')
            ->setHeader('Cache-Control: post-check=0, pre-check=0', false)
            ->setHeader('Pragma: no-cache');
        return $this;
    }

    /**
     * 
     * @param string $key
     * @return \Flare\Http\File|null
     */
    public function getFile($key)
    {
        return File::get($key);
    }

    /**
     * 
     * @param string $key
     * @return \Flare\Http\Files|null
     */
    public function getFiles($key)
    {
        return File::getMultiple($key);
    }

    /**
     * 
     * @param string $key
     * @return \Flare\Application\AbstractController
     */
    public function setDatabase($key = 'default')
    {
        $this->db = $this->getDatabase($key);
        return $this;
    }

    /**
     * 
     * @param string $key
     * @return \Flare\Db\Sql\Driver
     */
    public function getDatabase($key = null)
    {
        if ($key) {
            if (!$this->config->has("database.{$key}")) {
                show_error("'{$key}' doesn't exists in database configuration");
            }
            return Connection::create($key, $this->config->get("database.{$key}"));
        }
        return $this->db ? $this->db : null;
    }

    /**
     * 
     * @param string $helper
     * @return \Flare\Application\AbstractController
     */
    public function setHelper($helper)
    {
        $helper = strtolower($helper);
        if (file_exists(FLARE_DIR.'Flare/Helper/'.$helper.'.php')) {
            require_once FLARE_DIR.'Flare/Helper/'.$helper.'.php';
        } elseif (file_exists(F::getApp()->getModuleHelpersDirectory().$helper.'.php')) {
            require_once F::getApp()->getModuleHelpersDirectory().$helper.'.php';
        }
        return $this;
    }

    /**
     * 
     * @param boolean $switch
     * @return \Flare\Application\AbstractController
     */
    public function autoLayout($switch)
    {
        $this->config->set('layout.'.$this->request->getModule().'.auto', $switch);
        return $this;
    }

    /**
     * 
     * @return string
     */
    public function getAppDirectory()
    {
        return F::getApp()->getAppDirectory();
    }

    /**
     * 
     * @return string
     */
    public function getBaseDirectory()
    {
        return F::getApp()->getBaseDirectory();
    }

    /**
     *
     * @param string $url
     * @param int $code
     * @return void
     */
    public function redirect($url, $code = 302)
    {
        if (parse_url($url, PHP_URL_SCHEME) === null) {
            $url = $this->uri->base.trim($url, '/');
        }
        $this->response->setRedirect($url, $code)->send(false);
    }

    /**
     * 
     * @param string|array $params
     * @param string $controller
     * @param string $module
     * @param int $code
     * @return void
     */
    public function gotoAction($params, $controller = null, $module = null, $code = 302)
    {
        if (!$params) return;
        elseif (!is_array($params)) $params = (array) $params;

        $strClass = ($module ? $module : $this->request->getModule())
            .'.'.str_replace('/', '.', ($controller ? $controller : $this->request->getController()))
            .'.'.array_shift($params);

        $this->response->setRedirect($this->uri->create($strClass, $params), $code)->send(false);
    }

    /**
     *
     * @param string $url
     * @param int $code
     * @return void
     */
    public function redirectWithSuffix($url, $code = 302)
    {
        if ($this->config->get('router.url_suffix')) {
            $url .= '.'.$this->config->get('router.url_suffix');
        }
        $this->redirect($url, $code);
    }

    /**
     * 
     * @param array $params
     * @return void|boolean
     */
    public function back(array $params = array())
    {
        $url = $this->request->server('HTTP_REFERER', false);
        if ($url) {
            if ($params) {
                $parts = parse_url($url);
                if (!isset($parts['query'])) {
                    $parts['query'] = '';
                }
                parse_str($parts['query'], $query);
                $parts['query'] = http_build_query(array_merge($query, $params));
                if (!$parts['query']) {
                    unset($parts['query']);
                }
                $url = http_build_url($parts);
            }
            $this->redirect($url);
        }
        return false;
    }

    /**
     * 
     * @param string $action
     * @param string $controller
     * @param array $params
     * @return \Flare\View\Response|string|boolean|null
     */
    public function forward($action, $controller = null, array $params = array())
    {
        $action = $action.'_action';
        if ($action == '_action' || $action == $this->request->getActionMethodName()) return false;

        $path = F::getApp()->getModulesDirectory()
            .$this->request->getModule().'/'
            .F::getApp()->getControllersDirectoryName()
            .($controller ? $controller : $this->request->getController()).'.php';
        if (!file_exists($path)) {
            return false;
        }

        $class = ucwords($this->request->getModule())."\\Controllers\\";
        if (!$controller) {
            $class .= $this->request->getControllerClassName();
        } else {
            $controller = explode('/', $controller, 2);
            if (count($controller) === 2) {
                $controller[0] = ucwords($controller[0]);
                $controller[1] = str_replace(' ', '_', ucwords(str_replace('_', ' ', $controller[1]))).'_Controller';
                $class .= $controller[0]."\\".$controller[1];
            } else {
                $class .= str_replace(' ', '_', ucwords(str_replace('_', ' ', $controller[0]))).'_Controller';
            }
        }

        require_once $path;
        return call_user_func_array(array(new $class, $action), $params);
    }

    /**
     * 
     * @param string $path
     * @param array|null $data
     * @return \Flare\View\Response\Html
     */
    public function view($path, $data = null)
    {
        $html = new Html($path);
        $html->setIncludePath(F::getApp()->getModuleViewsDirectory())
            ->setLayoutPath(F::getApp()->getModuleLayoutsDirectory());
        $data = $data !== null ? $data : $this->data->toArray();
        if ($data) {
            $html->setData($data);
        }
        return $html->loadMiddleware();
    }

    /**
     * 
     * @param \Flare\Object\Json|\Flare\Util\Collection\|\ArrayObject|string|array $json
     * @return \Flare\View\Response\Json
     */
    public function viewAsJson($json)
    {
        return !($json instanceof Json) ? new Json($json) : $json;
    }

    /**
     * 
     * @param \Flare\Object\Xml|\Flare\Util\Collection\|\ArrayObject|string|array $xml
     * @return \Flare\View\Response\Xml
     */
    public function viewAsXml($xml)
    {
        return !($xml instanceof Xml) ? new Xml($xml) : $xml;
    }

    /**
     * 
     * @param int|callback $response
     * @return \Flare\Application\AbstractController
     */
    private function _requireMethod($response)
    {
        if (is_int($response)) {
            show_response($response);
        } elseif (is_callable($response)) {
            $response($this);
        }
        return $this;
    }

    /**
     * 
     * @param int|callback $response
     * @return \Flare\Application\AbstractController
     */
    public function requirePostMethod($response = 404)
    {
        if ($this->request->isPost()) {
            return $this;
        }
        return $this->_requireMethod($response);
    }

    /**
     * 
     * @param int|callback $response
     * @return \Flare\Application\AbstractController
     */
    public function requireGetMethod($response = 404)
    {
        if ($this->request->isGet()) {
            return $this;
        }
        return $this->_requireMethod($response);
    }

    /**
     * 
     * @param int|callback $response
     * @return \Flare\Application\AbstractController
     */
    public function requirePutMethod($response = 404)
    {
        if ($this->request->isPut()) {
            return $this;
        }
        return $this->_requireMethod($response);
    }

    /**
     * 
     * @param int|callback $response
     * @return \Flare\Application\AbstractController
     */
    public function requireDeleteMethod($response = 404)
    {
        if ($this->request->isDelete()) {
            return $this;
        }
        return $this->_requireMethod($response);
    }

    /**
     * 
     * @param int|callback $response
     * @return \Flare\Application\AbstractController
     */
    public function requireAjax($response = 404)
    {
        if ($this->request->isAjax()) {
            return $this;
        }
        return $this->_requireMethod($response);
    }

    /**
     * 
     * @param int|callback $response
     * @return \Flare\Application\AbstractController
     */
    public function requireHttps($response = 404)
    {
        if (!$this->uri->isHttps()) {
            return $this->_requireMethod($response);
        }
        return $this;
    }

    /**
     * 
     * @param int $redirectCode
     * @return \Flare\Application\AbstractController
     */
    public function forceHttps($redirectCode = 301)
    {
        $this->router->secure($redirectCode);
        return $this;
    }

    /**
     * 
     * @return void
     */
    abstract public function init();

    /**
     * 
     * @return void
     */
    abstract public function complete();

    /**
     * 
     * @return void
     */
    public function predispatch() {}

    /**
     * 
     * @return void
     */
    public function postdispatch() {}
}