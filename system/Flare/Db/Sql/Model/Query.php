<?php

namespace Flare\Db\Sql\Model;

use Flare\Db\Sql\Model\Collection;
use Flare\Db\Sql\Query\ARQuery;
use Flare\Db\Sql\Model;
use PDOException;
use PDO;

/**
 * 
 * @author anthony
 * 
 */
class Query extends ARQuery
{
    /**
     * 
     * @var \Flare\Db\Sql\Model
     */
    private $_model;

    /**
     * 
     * @param \Flare\Db\Sql\Model $model
     */
    public function __construct(Model &$model)
    {
        $this->_model = & $model;
        parent::__construct($this->_model->getAdapter());
        if ($this->_model->getAlias()) {
            $this->from(array(
                $this->_model->getAlias() => $this->_model->getTableName()
            ));
        } else {
            $this->from($this->_model->getTableName());
        }
    }

    /**
     * 
     * @return \Flare\Db\Sql\Model
     */
    public function & getModel()
    {
        return $this->_model;
    }

    /**
     * 
     * @return \Flare\Db\Sql\Model\Collection
     */
    public function getCollection()
    {
        $result = null;
        if (!$this->_select) $this->select();
        try {
            $pagination = null;
            if ($this->_page) {
                $pagination = $this->_paginate();
            }
            $stmt = $this->_conn->prepare($this->_compile());
            $stmt->execute();
            $this->_conn->printError($stmt);
            $result = new Collection(clone $this);
            if ($pagination) {
                $result->setPagination($pagination);
            }
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $result[] = with(clone $this->_model)->setAttributes($row)->setAsNew(false);
            }
            unset($newRow);
            $stmt = null;
        } catch (PDOException $ex) {
            show_error($ex->getMessage());
        }
        return $result;
    }

    /**
     * 
     * @return \Flare\Db\Sql\Model
     */
    public function getOne()
    {
        return $this->_getOne($this->_model);
    }

    /**
     * 
     * @param string $referenceClass
     * @param string $foreignKey
     * @param string $referenceField
     * @return \Flare\Db\Sql\Model\Query
     */
    public function with($referenceClass, $foreignKey = null, $referenceField = null)
    {
        if (!$foreignKey) {
            $foreignKeys = $this->_model->getForeignKeys();
            if (!$foreignKeys || !in_array($referenceClass, $foreignKeys)) {
                show_error("Undefined method 'with{$referenceClass}'");
            }
            $foreignKey = array_search($referenceClass, $foreignKeys);
        }
        
        $referenceClass = $this->_model->getNamespace().$referenceClass;
        $referenceClass = $referenceClass::create();
        if (!$referenceField) {
            $referenceField = $referenceClass->getIdField();
            if (!$referenceField) {
                show_error("'{$referenceClass->getClass()}' doesn't have primary key");
            }
        }

        $this->join(
            array($referenceClass->getTableName(), $referenceClass->getAlias()),
            $referenceClass->getFieldAlias($referenceField).' = '.$this->_model->getFieldAlias($foreignKey)
        );
        return $this;
    }

    /**
     * 
     * @param string $method
     * @param array $args
     * @return mixed
     */
    public function __call($method, $args)
    {
        if (strpos($method, '_') === 0) {
            show_error("Can't call private method");
        } elseif (strpos($method, 'with') === 0) {
            array_unshift($args, substr($method, 4));
            $method = 'with';
        } elseif (!method_exists($this, $method)) {
            show_error("Undefined method '{$method}'");
        }
        return call_user_func_array(array($this, $method), $args);
    }

    /**
     * 
     * @param array $set
     * @param int $limit
     * @param int $offset
     * @return int
     */
    public function update($set, $limit = null, $offset = null)
    {
        if ($limit) $this->limit($limit);
        if ($offset) $this->offset($offset);
        if ($pk = $this->_model->getIdField()) unset($set[$pk]);
        return $this->_update($this->_model->getTableName(), $set, true);
    }

    /**
     * 
     * @return void
     */
    public function __clone()
    {
        $this->clear()->from($this->_model->getTableName());
    }
}