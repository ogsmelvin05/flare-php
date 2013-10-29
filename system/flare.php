<?php

/**
 * 
 * @var string
 */
define('FLARE_DIR', dirname(__FILE__).'/');

/**
 * 
 * @var string
 */
define('FLARE_VERSION', '1.0');

if (!function_exists('flare_load_class')) {

    /**
     * 
     * @author anthony
     * @param string $class
     * @return void
     */
    function flare_load_class($class)
    {
        if (strpos($class, 'Flare') === 0) {
            require FLARE_DIR.str_replace("\\", '/', $class).'.php';
        }
    }
}

if (!function_exists('debug')) {

    /**
     * 
     * @author anthony
     * @param mixed $var
     * @param boolean $vardump
     * @return void
     */
    function debug($var, $vardump = false)
    {
        echo '<pre>';
        if (!$vardump) {
            if (empty($var)) {
                if (is_array($var)) {
                    $var = "[[ Empty array ]]";
                } elseif (is_string($var)) {
                    $var = "[[ Empty string ]]";
                } elseif (is_bool($var)) {
                    $var = "[[ Bool: false ]]";
                } elseif (is_null($var)) {
                    $var = "[[ NULL ]]";
                }
            }
            print_r($var);
        } else {
            var_dump($var);
        }
        echo '</pre>';
    }
}

if (!function_exists('http_build_url')) {

    /**
     * 
     * @author anthony
     * @param array $parsed_url
     * @return string
     */
    function http_build_url($parsed_url)
    {
        $scheme = isset($parsed_url['scheme']) ? $parsed_url['scheme'].'://' : '';
        $host = isset($parsed_url['host']) ? $parsed_url['host'] : '';
        $port = isset($parsed_url['port']) ? ':'.$parsed_url['port'] : '';
        $user = isset($parsed_url['user']) ? $parsed_url['user'] : '';
        $pass = isset($parsed_url['pass']) ? ':'.$parsed_url['pass']  : '';
        $pass = ($user || $pass) ? "$pass@" : '';
        $path = isset($parsed_url['path']) ? $parsed_url['path'] : '';
        $query = !empty($parsed_url['query']) ? '?'.$parsed_url['query'] : '';
        $fragment = !empty($parsed_url['fragment']) ? '#'.$parsed_url['fragment'] : '';
        return "$scheme$user$pass$host$port$path$query$fragment";
    }
}

if (!function_exists('get_image_types')) {

    /**
     * 
     * @author anthony
     * @return array
     */
    function get_image_types()
    {
        return array(
            IMAGETYPE_GIF,
            IMAGETYPE_JPEG,
            IMAGETYPE_PNG,
            IMAGETYPE_SWF,
            IMAGETYPE_PSD,
            IMAGETYPE_BMP,
            IMAGETYPE_TIFF_II,
            IMAGETYPE_TIFF_MM,
            IMAGETYPE_JPC,
            IMAGETYPE_JP2,
            IMAGETYPE_JPX,
            IMAGETYPE_JB2,
            IMAGETYPE_SWC,
            IMAGETYPE_IFF,
            IMAGETYPE_WBMP,
            IMAGETYPE_XBM,
            IMAGETYPE_ICO
        );
    }
}

if (!function_exists('get_image_mime_types')) {

    /**
     * 
     * @author anthony
     * @return array
     */
    function get_image_mime_types()
    {
        return array_map('image_type_to_mime_type', get_image_types());
    }
}

if (!function_exists('show_response')) {

    /**
     * 
     * @author anthony
     * @param int $code
     * @param string $message
     * @return void
     */
    function show_response($code, $message = '')
    {
        Flare\Flare::getApp()->error($code, $message);
    }
}

if (!function_exists('show_error')) {

    /**
     * 
     * @author anthony
     * @param string $message
     * @return void
     */
    function show_error($message)
    {
        Flare\Flare::getApp()->error(500, $message);
    }
}

if (!function_exists('_flare_show_exception')) {

    /**
     * 
     * @author anthony
     * @param \Exception $error
     * @return void
     */
    function _flare_show_exception($error)
    {
        show_error($error->getMessage());
    }
}

if (!function_exists('with')) {

    /**
     * 
     * @author anthony
     * @param mixed $object
     * @return mixed
     */
    function with($object)
    {
        return $object;
    }
}

set_exception_handler('_flare_show_exception');
spl_autoload_register('flare_load_class');

if (!class_exists("\\Flare\\Flare")) {
    require FLARE_DIR.'Flare/Flare.php';
}