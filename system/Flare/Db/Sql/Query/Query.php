<?php

namespace Flare\Db\Sql\Query;

use Flare\Object\Json;
use PDOException;
use PDO;

/**
 *
 * @author anthony
 *
 */
class Query
{
    /**
     *
     * @var string
     */
    private $_qstring;

    /**
     * 
     * @var array
     */
    private $_bindings = array();

    /**
     * 
     * @var \PDO
     */
    private $_conn;

    /**
     * 
     * @var array
     */
    private $_tables = array();

    /**
     * 
     * @var array
     */
    private $_columns = array();

    /**
     * 
     * @param string $sql
     * @param array $bindings
     * @param \PDO $conn
     */
    public function __construct(PDO &$conn, $sql = null, $bindings = null)
    {
        $this->_conn = & $conn;
        if ($sql) {
            $this->setQuery($sql);
        }
        if ($bindings) {
            $this->setBindings($bindings);
        }
    }

    /**
     * 
     * @param string $query
     * @return \Flare\Db\Sql\Query\Query
     */
    public function setQuery($query)
    {
        $this->_qstring = $query;
        return $this;
    }

    /**
     * 
     * @param array $bindings
     * @return \Flare\Db\Sql\Query\Query
     */
    public function setBindings($bindings)
    {
        foreach ($bindings as $key => $value) {
            $this->bind($key, $value);
        }
        return $this;
    }

    /**
     * 
     * @param array $columns
     * @return \Flare\Db\Sql\Query\Query
     */
    public function setColumns($columns)
    {
        foreach ($columns as $key => $value) {
            $this->column($key, $value);
        }
        return $this;
    }

    /**
     * 
     * @param array $tables
     * @return \Flare\Db\Sql\Query\Query
     */
    public function setTables($tables)
    {
        foreach ($tables as $key => $value) {
            if (isset($value[1])) {
                $this->table($key, $value[0], $value[1]);
            } else {
                $this->table($key, $value[0]);
            }
        }
        return $this;
    }

    /**
     * 
     * @param string $key
     * @param string|int $value
     * @return \Flare\Db\Sql\Query\Query
     */
    public function bind($key, $value)
    {
        $type = PDO::PARAM_STR;
        if (is_numeric($value)) {
            $type = PDO::PARAM_INT;
        }
        $this->_bindings[$key] = $this->_conn->quote($value, $type);
        return $this;
    }

    /**
     * 
     * @param string $key
     * @param string $value
     * @return \Flare\Db\Sql\Query\Query
     */
    public function column($key, $value)
    {
        $this->_columns[$key] = $this->_conn->quoteColumn($value);
        return $this;
    }

    /**
     * 
     * @param string $key
     * @param string $value
     * @param string $alias
     * @return \Flare\Db\Sql\Query\Query
     */
    public function table($key, $value, $alias = null)
    {
        if ($alias) {
            $this->_tables[$key] = $this->_conn->quoteAs($value, $alias);
        } else {
            $this->_tables[$key] = $this->_conn->quoteIdentifier($value);
        }
        return $this;
    }

    /**
     * 
     * @return string
     */
    private function _compile()
    {
        $str = $this->_qstring;
        if ($this->_tables) {
            $keys = array_keys($this->_tables);
            $values = array_values($this->_tables);
            $str = str_replace($keys, $values, $str);
        }
        if ($this->_columns) {
            $keys = array_keys($this->_columns);
            $values = array_values($this->_columns);
            $str = str_replace($keys, $values, $str);
        }
        if ($this->_bindings) {
            $keys = array_keys($this->_bindings);
            $values = array_values($this->_bindings);
            $str = str_replace($keys, $values, $str);
        }
        return trim($str);
    }

    /**
     * 
     * @return array
     */
    public function fetchAll()
    {
        $return = null;
        try {
            $stmt = $this->_conn->prepare($this->_compile());
            $stmt->execute();
            $this->_conn->printError($stmt);
            $return = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $stmt = null;
        } catch (PDOException $ex) {
            show_error($ex->getMessage());
        }
        return $return;
    }

    /**
     * 
     * @return int
     */
    public function execute()
    {
        $return = null;
        try {
            $stmt = $this->_conn->prepare($this->_compile());
            $stmt->execute();
            $this->_conn->printError($stmt);
            $return = $stmt->rowCount();
            $stmt = null;
        } catch (PDOException $ex) {
            show_error($ex->getMessage());
        }
        return $return;
    }

    /**
     *
     * @return array
     */
    public function getArray()
    {
        $result = null;
        try {
            $stmt = $this->_conn->prepare($this->_compile());
            $stmt->execute();
            $this->_conn->printError($stmt);
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $stmt = null;
        } catch (PDOException $ex) {
            show_error($ex->getMessage());
        }
        return $result;
    }

    /**
     * 
     * @return string
     */
    public function getJSON()
    {
        return json_encode($this->getArray());
    }

    /**
     * 
     * @return \Flare\Object\Json
     */
    public function getJSONObject()
    {
        return new JSON($this->getArray());
    }

    /**
     * 
     * @return string
     */
    public function __toString()
    {
        return $this->_compile();
    }
}