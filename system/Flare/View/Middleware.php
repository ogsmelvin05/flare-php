<?php

use Flare\Security\Xss;
use Flare\Flare as F;

/**
 * 
 * @author anthony
 * 
 */
class Html
{
    /**
     * 
     * @param string $value
     * @return string
     */
    public static function xss($value)
    {
        return Xss::filter($value);
    }

    /**
     * 
     * @param string $method
     * @param array $args
     * @return mixed
     */
    public static function __callStatic($method, $args)
    {
        $class = "Flare\\View\\UI\\".ucwords($method);
        try {
            $class = with(new ReflectionClass($class))->newInstanceArgs($args);
        } catch (LogicException $ex) {
            show_error("Undefined method '{$method}'");
        } catch (ReflectionException $ex) {
            show_error("Undefined method '{$method}'");
        }
        return $class;
    }
}

/**
 * 
 * @author anthony
 * 
 */
class Url
{
    /**
     * 
     * @param string $url
     * @return string
     */
    public static function base($url = null)
    {
        if (!$url) {
            $url = rtrim(F::$uri->base, '/');
        } elseif (parse_url($url, PHP_URL_SCHEME) === null) {
            $url = F::$uri->base.trim($url, '/');
        }
        return $url;
    }

    /**
     * 
     * @param string $url
     * @return string
     */
    public static function module($url = null)
    {
        if (!$url) {
            $url = rtrim(F::$uri->module, '/');
        } elseif (parse_url($url, PHP_URL_SCHEME) === null) {
            $url = F::$uri->module.trim($url, '/');
        }
        return $url;
    }

    /**
     * 
     * @param string $url
     * @return string
     */
    public static function submodule($url = null)
    {
        if (!$url) {
            $url = rtrim(F::$uri->submodule, '/');
        } elseif (parse_url($url, PHP_URL_SCHEME) === null) {
            $url = F::$uri->submodule.trim($url, '/');
        }
        return $url;
    }

    /**
     * 
     * @param string $module
     * @param string $controller
     * @param string $action
     * @return string
     */
    private static function _createUrl($module, $controller, $action)
    {
        $params = array();
        if ($action) {
            $params = explode('/', trim($action, '/'));
            $action = array_shift($params);
        } else {
            $action = F::$config->get('router.default_action');
        }

        if (!$controller) {
            $controller = F::$request->getController();
        } else {
            $controller = trim($controller, '/');
        }
        
        if (!$module) {
            $module = F::$request->getModule();
        } else {
            $module = trim($module, '/');
        }
        
        return F::$uri->create($module.'.'.str_replace('/', '.', $controller).'.'.$action, $params);
    }

    /**
     * 
     * @param string $action
     * @param string $controller
     * @param string $module
     * @return string
     */
    public static function action($action, $controller = null, $module = null)
    {
        return self::_createUrl($module, $controller, $action);
    }

    /**
     * 
     * @param string $controller
     * @param string $action
     * @param string $module
     * @return string
     */
    public static function controller($controller, $action = null, $module = null)
    {
        return self::_createUrl($module, $controller, $action);
    }
}

/**
 * 
 * @author anthony
 * 
 */
class Asset
{
    /**
     * 
     * @param string $url
     * @param string $cachebuster
     * @return string
     */
    public static function url($url, $cachebuster = null)
    {
        if (!$cachebuster) {
            $cachebuster = F::$config->get('assets_cache_buster');
        }
        return Url::base($url).(($cachebuster) ? '?'.ltrim($cachebuster, '?') : '');
    }

    /**
     * 
     * @param string $url
     * @param string $cachebuster
     * @return string
     */
    public static function js($url, $cachebuster = null)
    {
        return '<script type="text/javascript" src="'.self::url($url, $cachebuster).'"></script>';
    }

    /**
     * 
     * @param string $url
     * @param string $cachebuster
     * @return string
     */
    public static function css($url, $cachebuster = null)
    {
        return '<link media="screen" type="text/css" rel="stylesheet" href="'.self::url($url, $cachebuster).'"/>';
    }

    /**
     * 
     * @param string $url
     * @param string $class
     * @param string $id
     * @param string $cachebuster
     * @return string
     */
    public static function img($url, $class = null, $id = null, $cachebuster = null)
    {
        if ($class) {
            $class = 'class="'.$class.'" ';
        }
        if ($id) {
            $id = 'id="'.$id.'" ';
        }
        return '<img '.$class.$id.'src="'.self::url($url, $cachebuster).'"/>';
    }
}

/**
 * 
 * @author anthony
 * 
 */
class Section
{
    /**
     * 
     * @var array
     */
    private static $_sections = array();

    /**
     * 
     * @param string $name
     * @return void
     */
    public static function open($name)
    {
        end(self::$_sections);
        $lastSection = key(self::$_sections);
        if ($lastSection && self::$_sections[$lastSection] === true) {
            show_error("'{$lastSection}' section is currently open");
        } elseif (isset(self::$_sections[$name])) {
            show_error("'{$name}' section is already open");
        }
        self::$_sections[$name] = true;
        reset(self::$_sections);
        ob_start();
    }

    /**
     * 
     * @return void
     */
    public static function close()
    {
        end(self::$_sections);
        $name = key(self::$_sections);
        if (!isset(self::$_sections[$name])) {
            show_error("'{$name}' is not yet open");
        }
        reset(self::$_sections);
        self::$_sections[$name] = (string) ob_get_clean();
    }

    /**
     * 
     * @param string $name
     * @return string
     */
    public static function render($name)
    {
        if (!isset(self::$_sections[$name])) {
            return null;
        }
        return self::$_sections[$name];
    }
}

/**
 * 
 * @author anthony
 * 
 */
class Request
{
    /**
     * 
     * @param string $method
     * @param array $args
     * @return mixed
     */
    public static function __callStatic($method, $args)
    {
        return call_user_func_array(array(F::$request, $method), $args);
    }
}

/**
 * 
 * @author anthony
 * 
 */
class Config
{
    /**
     * 
     * @param string $method
     * @param array $args
     * @return mixed
     */
    public static function __callStatic($method, $args)
    {
        return call_user_func_array(array(F::$config, $method), $args);
    }
}

/**
 * 
 * @author anthony
 * 
 */
class Session
{
    /**
     * 
     * @param string $method
     * @param array $args
     * @return mixed
     */
    public static function __callStatic($method, $args)
    {
        return call_user_func_array(array(F::$session, $method), $args);
    }
}

/**
 * 
 * @author anthony
 * 
 */
class Cookie
{
    /**
     * 
     * @param string $method
     * @param array $args
     * @return mixed
     */
    public static function __callStatic($method, $args)
    {
        return call_user_func_array(array(F::$cookie, $method), $args);
    }
}

/**
 * 
 * @author anthony
 * 
 */
class Uri
{
    /**
     * 
     * @param string $method
     * @param array $args
     * @return mixed
     */
    public static function __callStatic($method, $args)
    {
        return call_user_func_array(array(F::$uri, $method), $args);
    }
}