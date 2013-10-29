<?php

if (!function_exists('now')) {
    
    /**
     * 
     * @param boolean $with_time
     * @return string
     */
    function now($with_time = true)
    {
        if ($with_time) {
            return date('Y-m-d H:i:s');
        }
        return date('Y-m-d');
    }
}

if (!function_exists('format_date')) {
    
    /**
     * Format date from MySql format to word format
     * @author anthony
     * @param string $date
     * @param string $format
     * @return string
     */
    function format_date($date, $format = "F d, Y")
    {
        $hour = 0;
        $min = 0;
        $sec = 0;
        $date = explode('-', $date);
        $day = isset($date[2]) ? $date[2] : '00';
        if (strlen($day) > 2) {
            $day = explode(' ', $day);
            if (isset($day[1])) {
                $time = explode(':', $day[1]);
                $hour = (int) $time[0];
                $min = (int) $time[1];
                $sec = (int) $time[2];
            }
            $day = $day[0];
        }
        $month = isset($date[1]) ? $date[1] : '00';
        $year = isset($date[0]) ? $date[0] : '0000';
        return date($format, mktime($hour, $min, $sec, $month, $day, $year));
    }
}

if (!function_exists('time_elapsed')) {

    /**
     * Param must be in timestamp
     * @param string|int $time
     * @param string|int $current_time
     * @return string
     */
    function time_elapsed($ptime, $current_time = null)
    {
        if (is_string($ptime)) {
            $ptime = strtotime($ptime);
        }

        if (!$current_time) {
            $current_time = time();
        } elseif (is_string($current_time)) {
            $current_time = strtotime($current_time);
        }

        $etime = $current_time - $ptime;
        
        if ($etime < 1) {
            return '0 seconds';
        }
        
        $a = array( 12 * 30 * 24 * 60 * 60  =>  'year',
                    30 * 24 * 60 * 60      =>  'month',
                    24 * 60 * 60            =>  'day',
                    60 * 60              =>  'hour',
                    60                    =>  'minute',
                    1                      =>  'second'
                    );
        
        foreach ($a as $secs => $str) {
            $d = $etime / $secs;
            if ($d >= 1) {
                $r = round($d);
                return $r . ' ' . $str . ($r > 1 ? 's' : '');
            }
        }
    }
}