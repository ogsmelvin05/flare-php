<?php

return array(

    /**
     * Define modules
     */
    'modules' => array('main'),

    /**
     * To return it to its default value
     * just give a value NULL
     */
    'timezone' => 'Asia/Manila',

    /**
     * To return it to its default value
     * just give a value ''
     */
    'time_limit' => '',

    /**
     * To return it to its default value
     * just give a value ''
     */
    'memory_limit' => '',

    /**
     * Global Request XSS Filter
     */
    'request_xss_filter' => false,

    /**
     * Global Action Parameters XSS Filter
     */
    'action_xss_filter' => false,

    /**
     * Cache buster for assets ex. /js/script.js?v=12
     */
    'assets_cache_buster' => '',

    /**
     * GZIP output compression
     */
    'auto_compress' => true,

    /**
     * Default response content type
     */
    'default_content_type' => 'text/html',

    /**
     * Allow override of config in controller
     */
    'allow_override' => true

);
