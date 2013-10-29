<?php

namespace Flare\Db\Sql;

use PDOStatement;
use PDO;

/**
 * 
 * @author anthony
 * 
 */
abstract class Driver extends PDO
{
    /**
     *
     * @var string
     */
    protected $_quote = '`';

    /**
     * 
     * @param string $table
     * @param boolean $get_from_cache
     * @return array
     */
    abstract public function getColumns($table, $get_from_cache = false);

    /**
     * 
     * @param string $table
     * @return string
     */
    abstract public function getPrimaryKey($table);

    /**
     *
     * @param string $name
     * @param string $alias
     * @return string
     */
    abstract public function quoteAs($name, $alias);

    /**
     *
     * @param string $name
     * @return string
     */
    abstract public function quoteIdentifier($name);

    /**
     *
     * @param string $field
     * @param string $alias
     * @return string
     */
    abstract public function quoteColumn($field);

    /**
     *
     * @return string
     */
    abstract public function getQuote();

    /**
     * 
     * @param string $query
     * @param array $bindings
     * @return \Flare\Db\Sql\Query\Query
     */
    abstract public function sql($query = null, $bindings = null);

    /**
     * 
     * @param string $method
     * @param array $args
     * @return mixed
     */
    abstract protected function _execute($method, array $args);

    /**
     * 
     * @param \PDOStatement $stmt
     * @return void
     */
    abstract public function printError(PDOStatement $stmt);

    /**
     * 
     * @param string $method
     * @param array $args
     * @return mixed
     */
    public function __call($method, $args)
    {
        return $this->_execute($method, $args);
    }
}
