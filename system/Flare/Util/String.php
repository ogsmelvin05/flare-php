<?php

namespace Flare\Util;

use Flare\Security\Xss;

/**
 * 
 * @author anthony
 * 
 */
class String
{
    /**
     * 
     * @var string
     */
    protected $content;

    /**
     * 
     * @param string $content
     */
    protected function __construct($content)
    {
        $this->content = $content;
    }

    /**
     * 
     * @param string $string
     * @return \Flare\Util\String
     */
    public static function build($string)
    {
        return new self($string);
    }

    /**
     * 
     * @param int $length
     * @param int $position
     * @param string $symbol
     * @return string
     */
    public function ellipsize($length = 140, $position = 1, $symbol = '&hellip;')
    {
        $this->xss();
        $this->content = trim($this->content);
        if (strlen($this->content) <= $length) {
            return $this;
        }
        $beg = substr($this->content, 0, floor($length * $position));
        $position = ($position > 1) ? 1 : $position;
        if ($position === 1) {
            $end = substr($this->content, 0, -($length - strlen($beg)));
        } else {
            $end = substr($this->content, -($length - strlen($beg)));
        }
        $this->content = $beg.$symbol.$end;
        return $this;
    }

    /**
     * 
     * @return \Flare\Util\String
     */
    public function xss()
    {
        $this->content = Xss::filter($this->content);
        return $this;
    }

    /**
     * 
     * @return string
     */
    public function __toString()
    {
        return $this->content;
    }
}