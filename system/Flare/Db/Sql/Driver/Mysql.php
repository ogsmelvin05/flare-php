<?php

namespace Flare\Db\Sql\Driver;

use Flare\Db\Sql\Query\ARQuery;
use Flare\Db\Sql\Query\Query;
use Flare\Db\Sql\Driver;
use PDOStatement;
use PDO;

/**
 *
 * @author anthony
 *
 */
class Mysql extends Driver
{
    /**
     * 
     * @var array
     */
    private static $_metaCache = array();

    /**
     *
     * @param string $str
     * @param int $parameter_type
     * @return string
     */
    public function quote($value, $parameter_type = PDO::PARAM_STR)
    {
        if ($value === null) {
            return "NULL";
        }
        return parent::quote($value, $parameter_type);
    }

    /**
     *
     * @param string $name
     * @param string $alias
     * @return string
     */
    public function quoteAs($name, $alias)
    {
        return "{$name} AS ".$this->quoteIdentifier($alias);
    }

    /**
     *
     * @param string $name
     * @return string
     */
    public function quoteIdentifier($name)
    {
        if ($name === '*') {
            return $name;
        }
        return $this->_quote.$name.$this->_quote;
    }

    /**
     *
     * @param string $field
     * @param string $alias
     * @return string
     */
    public function quoteColumn($field)
    {
        $field = explode('.', $field);
        if (isset($field[1])) {
            $field[1] = $this->quoteIdentifier($field[1]);
        }
        $field[0] = $this->quoteIdentifier($field[0]);
        return implode('.', $field);
    }

    /**
     *
     * @return string
     */
    public function getQuote()
    {
        return $this->_quote;
    }

    /**
     * 
     * @param string $query
     * @param array $bindings
     * @return \Flare\Db\Sql\Query\Query
     */
    public function sql($query = null, $bindings = null)
    {
        return new Query($this, $query, $bindings);
    }

    /**
     * 
     * @param string $table
     * @return string
     */
    public function getPrimaryKey($table)
    {
        $info = $this->sql("SHOW INDEX FROM :table where Key_name = 'PRIMARY'")
            ->table(':table', $table)
            ->fetchAll();
        return isset($info[0]['Column_name']) ? $info[0]['Column_name'] : null;
    }

    /**
     * 
     * @param string $table
     * @param boolean $get_from_cache
     * @return array
     */
    public function getColumns($table, $get_from_cache = false)
    {
        if ($get_from_cache && !empty(self::$_metaCache[$table]['columns'])) {
            return self::$_metaCache[$table]['columns'];
        }

        $columns = $this->sql("DESCRIBE :table")
            ->table(':table', $table)
            ->fetchAll();

        $fields = array();
        foreach ($columns as $column) {
            $fields[] = $column['Field'];
        }
        unset($columns);

        if ($get_from_cache) {
            if (!isset(self::$_metaCache[$table])) {
                self::$_metaCache[$table] = array();
            }
            self::$_metaCache[$table]['columns'] = $fields;
        }
        return $fields;
    }

    /**
     * 
     * @param \PDOStatement $stmt
     * @return void
     */
    public function printError(PDOStatement $stmt)
    {
        $error = $stmt->errorInfo();
        if (!empty($error[1]) || !empty($error[2])) {
            show_response(500, $error[2]);
        }
        return;
    }

    /**
     * 
     * @param string $method
     * @param array $args
     * @return mixed
     */
    protected function _execute($method, array $args)
    {
        $arQuery = new ARQuery($this);
        if (!method_exists($arQuery, $method)) {
            show_error("'{$method}' doesn' exists");
        }
        return call_user_func_array(array($arQuery, $method), $args);
    }
}