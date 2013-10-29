<?php

namespace Flare\Db\Sql\Result;

use Flare\Util\Collection as ParentCollection;
use Flare\View\Util\Pagination;
use Flare\Object\Json;

/**
 *
 * @author anthony
 *
 */
class Collection extends ParentCollection
{
    /**
     * 
     * @var \Flare\View\Pagination
     */
    protected $_pagination;

    /**
     *
     * @param \Flare\Db\Sql\Driver $conn
     * @param int $count
     * @param array $rows
     */
    public function __construct($rows = array())
    {
        parent::__construct($rows);
    }

    /**
     * 
     * @param \Flare\View\Pagination $pagination
     * @return \Flare\Db\Sql\Result\Collection
     */
    public function setPagination(Pagination $pagination)
    {
        $this->_pagination = $pagination;
        return $this;
    }

    /**
     * 
     * @return \Flare\View\Pagination
     */
    public function getPagination()
    {
        return $this->_pagination;
    }

    /**
     * 
     * @return string
     */
    public function toJSON()
    {
        $array = array();
        foreach ($this as $key => $value) {
            $array[] = $value->toArray();
        }
        return json_encode($array);
    }

    /**
     * 
     * @return \Flare\Object\Json
     */
    public function toJSONObject()
    {
        $array = array();
        foreach ($this as $key => $value) {
            $array[] = $value->toArray();
        }
        return new Json($array);
    }

    /**
     * 
     * @return array
     */
    public function toArray()
    {
        $array = array();
        foreach ($this as $key => $value) {
            $array[] = $value->toArray();
        }
        return $array;
    }

    /**
     * 
     * @return string
     */
    public function toXML()
    {
        //TODO
    }

    /**
     * 
     * @return \Flare\Object\Xml
     */
    public function toXMLObject()
    {
        //TODO
    }
}