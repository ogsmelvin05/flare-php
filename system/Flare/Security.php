<?php

namespace Flare;

/**
 * 
 * @author anthony
 * 
 */
class Security
{
    /**
     * 
     * @param string $str
     * @param boolean $urlEncoded
     * @return string
     */
    public static function removeInvisibleChars($str, $urlEnconded = true)
    {
        $non_displayables = array();
        if ($urlEnconded) {
            $non_displayables[] = '/%0[0-8bcef]/';
            $non_displayables[] = '/%1[0-9a-f]/';
        }
        $non_displayables[] = '/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]+/S';
        do {
            $str = preg_replace($non_displayables, '', $str, -1, $count);
        } while($count);
        return $str;
    }
}