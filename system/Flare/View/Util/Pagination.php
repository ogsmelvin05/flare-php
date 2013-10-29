<?php

namespace Flare\View\Util;

use Flare\View\UI;

/**
 * 
 * @author anthony
 * 
 */
class Pagination extends UI
{
    /**
     * 
     * @var boolean
     */
    private $_includeQueryString = false;

    /**
     * 
     * @var int
     */
    private $_page;

    /**
     * 
     * @var int
     */
    private $_offset;

    /**
     * 
     * @var int
     */
    private $_limit;

    /**
     * 
     * @var int
     */
    private $_total;

    /**
     * 
     * @var string
     */
    private $_url;

    /**
     * 
     * @param int $page
     * @return \Flare\View\Util\Pagination
     */
    public function setCurrentPage($page)
    {
        $this->_page = (int) $page;
        return $this;
    }

    /**
     * 
     * @param int $limit
     * @return \Flare\View\Util\Pagination
     */
    public function setLimitPerPage($limit)
    {
        $this->_limit = (int) $limit;
        return $this;
    }

    /**
     * 
     * @return int
     */
    public function getLimitPerPage()
    {
        return $this->_limit;
    }

    /**
     * 
     * @param int $count
     * @return \Flare\View\Util\Pagination
     */
    public function setTotalCount($count)
    {
        $this->_total = (int) $count;
        return $this;
    }

    /**
     * 
     * @return int
     */
    public function getTotalCount()
    {
        return $this->_total;
    }

    /**
     * 
     * @return int
     */
    public function getCurrentPage()
    {
        return $this->_page;
    }

    /**
     * 
     * @param int $offset
     * @return \Flare\View\Util\Pagination
     */
    public function setOffset($offset)
    {
        $this->_offset = (int) $offset;
        return $this;
    }

    /**
     * 
     * @return int
     */
    public function getOffset()
    {
        return $this->_offset;
    }

    /**
     * 
     * @param string $url
     * @return \Flare\View\Util\Pagination
     */
    public function setBaseUrl($url)
    {
        $this->_url = $url;
        return $this;
    }

    /**
     * 
     * @return string
     */
    public function getBaseUrl()
    {
        return $this->_url;
    }

    /**
     * 
     * @param boolean $switch
     * @return \Flare\View\Util\Pagination
     */
    public function includeQueryString($switch = true)
    {
        $this->_includeQueryString = (boolean) $switch;
        return $this;
    }

    /**
     * 
     * @return string
     */
    public function getId()
    {
        return $this->_id;
    }

    /**
     * 
     * @return string
     */
    public function render()
    {
        $list = '';
        if (!$this->_total || !$this->_limit) {
            return $this->_wrap($list);
        }

        $num_pages = ceil($this->_total / $this->_limit);
        if (!$num_pages || (int) $num_pages === 1) {
            return $this->_wrap($list);
        }
        $start = (($this->_page - 4) > 0) ? $this->_page - 4 : 1;
        $end = (($this->_page + 4) < $num_pages) ? $this->_page + 4 : $num_pages;
        $url = parse_url($this->_url);

        $vars = array();
        if (isset($url['query'])) {
            parse_str($url['query'], $vars);
        }

        if ($this->_includeQueryString && $_GET) {
            $vars = array_merge($vars, $_GET);
        }

        unset($vars['page']);
        $vars['page'] = '';
        $url['query'] = http_build_query($vars);
        $url = http_build_url($url);

        if ($this->_page > 1) {
            $list .= "<li><a data-page=\"1\" href=\"{$url}1\">First</a></li>";
            $list .= "<li><a data-page=\"".($this->_page - 1)."\" href=\"{$url}".($this->_page - 1)."\">Prev</a></li>";
        }
        foreach (range($start, $end) as $page) {
            if ($this->_page == $page) {
                $list .= "<li class=\"active\"><a data-page=\"{$page}\" href=\"{$url}{$page}\">".$page."</a></li>";
            } else {
                $list .= "<li><a data-page=\"{$page}\" href=\"{$url}{$page}\">".$page."</a></li>";
            }
        }
        if ($this->_page < $num_pages) {
            $list .= "<li><a data-page=\"".($this->_page + 1)."\" href=\"{$url}".($this->_page + 1)."\">Next</a></li>";
            $list .= "<li><a data-page=\"{$num_pages}\" href=\"{$url}{$num_pages}\">Last</a></li>";
        }
        return $this->_wrap($list);
    }

    /**
     * 
     * @param string $list
     * @return string
     */
    private function _wrap($list)
    {
        $wrapper = "<ul class=\"pagination";
        if ($this->_classes) {
            $wrapper .= ' '.implode(' ', $this->_classes);
        }
        $wrapper .= "\"";
        if ($this->_style) {
            $wrapper .= " style=\"";
            foreach ($this->_style as $key => $val) {
                $wrapper .= "{$key}: {$val};";
            }
            $wrapper .= "\"";
        }
        if ($this->_id) {
            $wrapper .= " id=\"{$this->_id}\"";
        }
        $wrapper .= ">{$list}</ul>";
        return $wrapper;
    }
}