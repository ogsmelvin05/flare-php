<?php

namespace Flare;

use Flare\Application;

/**
 *
 * @author anthony
 *
 */
class Flare
{
    /**
     *
     * @var \Flare\Http\Request
     */
    public static $request;

    /**
     *
     * @var \Flare\Http\Response
     */
    public static $response;

    /**
     *
     * @var \Flare\Http\Uri
     */
    public static $uri;

    /**
     *
     * @var \Flare\Http\Session
     */
    public static $session;

    /**
     *
     * @var \Flare\Http\Cookie
     */
    public static $cookie;

    /**
     *
     * @var \Flare\Application\Config
     */
    public static $config;

    /**
     * 
     * @var \Flare\Application\Router
     */
    public static $router;

    /**
     *
     * @var \Flare\Application
     */
    private static $_application;
    
    /**
     * 
     * @return \Flare\Application
     */
    public static function createApp()
    {
        if (self::$_application) {
            show_error('Flare Application is already created');
        }
        self::$_application = new Application();
        return self::$_application;
    }

    /**
     *
     * @return \Flare\Application
     */
    public static function getApp()
    {
        return self::$_application;
    }
}