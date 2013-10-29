<?php

if (!function_exists('one_space')) {

    /**
     * 
     * @param string $text
     * @return string
     */
    function one_space($text)
    {
        return preg_replace('/\s+/', ' ', $text);
    }
}

if (!function_exists('title_case')) {

    /**
     * 
     * @author anthony
     * @param string $string
     * @return string
     */
    function title_case($string)
    {
        return ucwords(str_replace('_', ' ', strtolower($string)));
    }
}

if (!function_exists('format_number')) {

    /**
     * 
     * @author anthony
     * @param string|int $number
     * @param string $thousand_sep
     * @param string $dec_sep
     * @return string
     */
    function format_number($number, $decimals = 0, $dec_sep = '.', $thousand_sep = ',')
    {
        return number_format($number, $decimals, $dec_sep, $thousand_sep);
    }
}

if (!function_exists('unique_code')) {

    /**
     * 
     * @author anthony
     * @param int $length
     * @return string
     */
    function unique_code($length = 8)
    {
        return strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, $length));
    }
}