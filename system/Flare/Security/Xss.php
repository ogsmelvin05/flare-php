<?php

namespace Flare\Security;

use Flare\Security;

/**
 * 
 * @author anthony
 * 
 */
class Xss extends Security
{
    /**
     * 
     * @var array
     */
    private static $_defaultOptions = array(
        'strip_tags' => false,
        'encoding' => 'UTF-8',
        'flags' => ENT_QUOTES,
        'double_encode' => true
    );

    /**
     * 
     * @param string|array $value
     * @param array $options
     * @return string|array
     */
    public static function filter($value, $options = array())
    {
        if ($options) {
            $options = array_merge(self::$_defaultOptions, $options);
        } else {
            $options = self::$_defaultOptions;
        }
        
        if (is_array($value)) {
            foreach ($value as &$val) {
                $val = self::filter($val, $options);
            }
            return $value;
        }
        if ($options['strip_tags']) {
            $value = strip_tags($value);
        }
        return htmlentities((string) $value, $options['flags'], $options['encoding'], $options['double_encode']); 
    }

    /**
     * 
     * @return array
     */
    public static function getDefaultOptions()
    {
        return self::$_defaultOptions;
    }
}