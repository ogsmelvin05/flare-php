<?php

namespace Flare\Db\Sql\Model;

use Flare\Db\Sql\Result\Collection as ParentCollection;
use Flare\Db\Sql\Model\Query;

/**
 * 
 * @author anthony
 * 
 */
class Collection extends ParentCollection
{
    /**
     * 
     * @var \Flare\Db\Sql\Model\Query
     */
    private $_query;

    /**
     * 
     * @var array
     */
    private $_fields;

    /**
     * 
     * @param \Flare\Db\Sql\Model\Query $query
     * @param array $content
     */
    public function __construct(Query $query, array $content = array())
    {
        parent::__construct($content);
        $this->_query = $query;
    }

    /**
     * 
     * @param string $field
     * @param string|int $value
     * @return \Flare\Db\Sql\Model\Collection
     */
    public function setField($field, $value)
    {
        $this->_fields[$field] = $value;
        return $this;
    }

    /**
     * 
     * @return void
     */
    private function _resetQuery()
    {
        $this->_query->clear()->from($this->_query->getModel()->getTableName());
    }

    /**
     * 
     * @return int
     */
    public function save()
    {
        $result = 0;
        if ($idField = $this->_query->getModel()->getIdField()) {
            $ids = array();
            foreach ($this as $row) {
                if (!isset($row[$idField])) continue;
                $ids[] = $row[$idField];
                $row->setAttributes(array_merge($row->toArray(), $this->_fields));
            }
            $result = $this->_query->whereIn($idField, $ids)
                ->update($this->_query->getModel()->getTableName(), $this->_fields);
            $this->_resetQuery();
            unset($ids);
        }
        return $result;
    }

    /**
     * 
     * @return int
     */
    public function delete()
    {
        $result = 0;
        if ($idField = $this->_query->getModel()->getIdField()) {
            $ids = array();
            foreach ($this as $key => $row) {
                if (!isset($row[$idField])) continue;
                $ids[] = $row[$idField];
                unset($this[$key]);
            }
            $result = $this->_query->whereIn($idField, $ids)->delete();
            $this->_resetQuery();
            unset($ids);
        }
        return $result;
    }
}