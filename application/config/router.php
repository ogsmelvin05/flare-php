<?php

return array(

    /**
     * 
     * Force HTTPS
     */
    'force_https' => false,

    /**
     * Custom url using the following format
     * :url => module.controller.action OR :url => module.submodule.controller.action
     *
     * ex.
     * 'routes' => array(
     *   '/welcome/home' => 'module.submodule.controller.action'
     *  )
     * 
     * OR
     * 
     * 'routes' => array(
     *   '/welcome/home' => 'module.controller.action'
     *  )
     */
    'routes' => array(),

    /**
     * to use the built in just leave the value blank
     * statusCode => 'module.controller.action'
     * 
     * You can also do this for wildcard error controller
     * 'errors' => 'module.controller.action'
     */
    'errors' => 'main.error.index',

    /**
     * Default module, controller and action
     * to be loaded
     */
    'default_module' => 'main',
    'default_controller' => 'index',
    'default_action' => 'index',

    /**
     * Leave blank for default
     * /home.[url_suffix]
     */
    'url_suffix' => ''
    
);