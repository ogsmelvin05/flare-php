<?php

namespace Flare\Security;

use Flare\Security;

/**
 * 
 * @author anthony
 * 
 */
class Uri extends Security
{
    /**
     * 
     * @var string
     */
    const INVALID_URI_CHARS = 'a-z 0-9~%.:_\-';

    /**
     * 
     * @param string $uri
     * @param array $segments
     * @param string $invalid
     */
    public static function validate(&$uri, &$segments, $invalidChars = null)
    {
        if (!$invalidChars) {
            $invalidChars = self::INVALID_URI_CHARS;
        }
        $uri = self::removeInvisibleChars($uri, false);
        if (!is_array($segments)) {
            $segments = array();
        }
        foreach (explode('/', $uri) as $segment) {
            if ($segment == '') {
                $segments[] = $segment;
            } elseif (!preg_match("|^[".str_replace(array('\\-', '\-'), '-', preg_quote($invalidChars, '-'))."]+$|i", $segment)) {
                return false;
            } else {
                $segments[] = self::filter($segment);
            }
        }
        return true;
    }

    /**
     * 
     * @param string $uri
     * @return string
     */
    public static function filter($uri)
    {
        $bad = array('$', '(', ')', '%28', '%29');
        $good = array('&#36;', '&#40;', '&#41;', '&#40;', '&#41;');
        return str_replace($bad, $good, $uri);
    }

    /**
     * 
     * @param string|array $data
     * @return string|array
     */
    public static function encode($data)
    {
        if (is_array($data)) {
            foreach ($data as &$d) {
                $d = self::encode($d);
            }
            return $data;
        }
        return urlencode($data);
    }

    /**
     * 
     * @param string|array $data
     * @return string|array
     */
    public static function decode($data)
    {
        if (is_array($data)) {
            foreach ($data as &$d) {
                $d = self::decode($d);
            }
            return $data;
        }
        return urldecode($data);
    }
}