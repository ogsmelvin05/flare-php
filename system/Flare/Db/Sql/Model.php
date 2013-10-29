<?php

namespace Flare\Db\Sql;

use Flare\Db\Model as ParentModel;
use Flare\Db\Sql\Model\Relation;
use Flare\Db\Sql\Model\Query;
use Flare\Security\Xss;
use Flare\Util\String;
use ArrayAccess;
use Iterator;

/**
 * 
 * @author anthony
 * 
 */
abstract class Model extends ParentModel implements ArrayAccess, Iterator
{
    /**
     * 
     * @var string
     */
    protected $table;

    /**
     * 
     * @var string
     */
    protected $primaryKey;

    /**
     * 
     * @var string
     */
    private $namespace;

    /**
     * 
     * @var string
     */
    private $idField;

    /**
     * 
     * @var array
     */
    protected $foreignKeys = array();

    /**
     * 
     * @var array
     */
    protected $attributes = array();

    /**
     * 
     * @var string
     */
    protected $alias;

    /**
     * 
     * @var array
     */
    protected $fields = array();

    /**
     * 
     * @var \Flare\Db\Sql\Driver
     */
    private static $adapter;

    /**
     * 
     * @var string
     */
    private $class;

    /**
     * 
     * @var boolean
     */
    private $new = true;

    /**
     * 
     * @var array
     */
    private static $metaCache = array();

    /**
     * 
     * @var boolean
     */
    private $isObject;

    /**
     * 
     * @var boolean
     */
    private $isTrashed = false;

    /**
     * 
     * @param array $data
     * @param boolean $isObject
     */
    protected function __construct(array $data = array(), $isObject = false)
    {
        if (!self::$adapter) {
            self::$adapter = self::_getController()->getDatabase();
            if (!self::$adapter) {
                show_error("Doesn't have database connection");
            }
        }
        $this->isObject = $isObject;
        $this->_init($data);
    }

    /**
     * 
     * @return mixed
     */
    public function rewind()
    {
        return reset($this->attributes);
    }

    /**
     * 
     * @return mixed
     */
    public function current()
    {
        return current($this->attributes);
    }

    /**
     * 
     * @return mixed
     */
    public function key()
    {
        return key($this->attributes);
    }

    /**
     * 
     * @return mixed
     */
    public function next()
    {
        return next($this->attributes);
    }

    /**
     * 
     * @return boolean
     */
    public function valid()
    {
        return key($this->attributes) !== null;
    }

    /**
     * 
     * @param string $index
     * @return boolean
     */
    public function offsetExists($index)
    {
        return $this->__isset($index);
    }
    
    /**
     * 
     * @param string $index
     * @return mixed
     */
    public function offsetGet($index)
    {
        return $this->getAttribute($index);
    }
    
    /**
     * 
     * @param string $index
     * @param mixed $value
     * @return void
     */
    public function offsetSet($index, $value)
    {
        if ($index !== null) $this->setAttribute($index, $value);
    }
    
    /**
     * 
     * @param string $index
     * @return void
     */
    public function offsetUnset($index)
    {
        $this->__unset($index);
    }

    /**
     * 
     * @param boolean $new
     * @return \Flare\Db\Sql\Model
     */
    public function setAsNew($new = true)
    {
        $this->new = $new;
        return $this;
    }

    /**
     * 
     * @return boolean
     */
    public function isNew()
    {
        return $this->new;
    }

    /**
     * 
     * @return string
     */
    public function getNamespace()
    {
        return $this->namespace;
    }

    /**
     * 
     * @return string
     */
    protected function getAlias()
    {
        return $this->alias;
    }

    /**
     * 
     * @param string $name
     * @param boolean $withQuote
     * @return string
     */
    protected function getFieldAlias($name, $withQuote = false)
    {
        $quote = $withQuote ? self::$adapter->getQuote() : '';
        if (!$this->alias) {
            return $quote.$this->table.$quote.'.'.$quote.$name.$quote;
        }
        return $quote.$this->alias.$quote.'.'.$quote.$name.$quote;
    }

    /**
     * 
     * @param array $data
     * @return void
     */
    private function _init(array $data)
    {
        if (empty($this->table)) {
            show_error(get_class($this)." table must be defined");
        }

        $this->class = get_class($this);
        $namespace = explode("\\", $this->class);
        array_pop($namespace);
        $this->namespace = implode("\\", $namespace)."\\";
       
        if (!isset(self::$metaCache[$this->class])) {
            self::$metaCache[$this->class] = array();
            if (!empty($this->primaryKey)) {
                self::$metaCache[$this->class]['primary_key'] = $this->primaryKey;
            } else {
                self::$metaCache[$this->class]['primary_key'] = self::$adapter->getPrimaryKey($this->table);
            }
            self::$metaCache[$this->class]['foreign_keys'] = $this->foreignKeys;
        }

        if (!$this->fields) {
            $this->fields = self::$adapter->getColumns($this->table, true);
        }
        if ($field = $this->getPrimaryKey()) {
            $this->setIdField($field);
        }
        if ($data) {
            $this->setAttributes($data);
        }
    }

    /**
     * 
     * @return array
     */
    protected function getFields()
    {
        return $this->fields;
    }

    /**
     * 
     * @param string $field
     * @param string|array
     */
    public function xss($field)
    {
        $value = $this->getAttribute($field);
        if ($value !== null) {
            $value = Xss::filter($value);
        }
        return $value;
    }

    /**
     * 
     * @param string $field
     * @return int
     */
    public function int($field)
    {
        $value = $this->getAttribute($field);
        if ($value !== null) {
            $value = intval($value);
        }
        return $value;
    }

    /**
     * 
     * @param string $field
     * @param boolean $xss
     * @return string
     */
    public function string($field, $xss = true)
    {
        if ($xss) {
            return $this->xss($field);
        }
        $value = $this->getAttribute($field);
        if ($value !== null) {
            $value = (string) $value;
        }
        return $value;
    }

    /**
     * 
     * @param string $field
     * @param string $format
     * @return string
     */
    public function date($field, $format = 'Y-m-d H:i:s')
    {
        $value = $this->getAttribute($field);
        if ($value !== null) {
            $value = date($format, strtotime($value));
        }
        return $value;
    }

    /**
     * 
     * @param string $field
     * @param int $decimals
     * @param string $dec_sep
     * @param string $thousand_sep
     * @return string
     */
    public function number($field, $decimals = 0, $dec_sep = '.', $thousand_sep = ',')
    {
        $value = $this->getAttribute($field);
        if ($value !== null) {
            $value = number_format($value, $decimals, $dec_sep, $thousand_sep);
        }
        return $value;
    }

    /**
     * 
     * @param string $field
     * @return float
     */
    public function float($field)
    {
        $value = $this->getAttribute($field);
        if ($value !== null) {
            $value = floatval($value);
        }
        return $value;
    }

    /**
     * 
     * @return string
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * 
     * @return string
     */
    protected function getTableName()
    {
        return $this->table;
    }

    /**
     * 
     * @param string $field
     * @param int $length
     * @param int $position
     * @param string $symbol
     * @return string
     */
    public function ellipsize($field, $length = 140, $position = 1, $symbol = '&hellip;')
    {
        $value = $this->getAttribute($field);
        if ($value !== null) {
            return String::build($value)->ellipsize($length, $position, $symbol);
        }
        return $value;
    }

    /**
     * 
     * @return string
     */
    protected function getForeignKeys()
    {
        return self::$metaCache[$this->class]['foreign_keys'];
    }

    /**
     * 
     * @return string
     */
    protected function getPrimaryKey()
    {
        if (!isset(self::$metaCache[$this->class]['primary_key'])) {
            return null;
        }
        return self::$metaCache[$this->class]['primary_key'];
    }

    /**
     * 
     * @return string|int
     */
    public function save()
    {
        if ($this->new) {
            $success = self::$adapter->insert($this->table, $this->attributes);
            if ($result !== null) {
                $this->setAsNew(false);
            }
            return $result;
        }
        
        if (!($id = $this->getId())) {
            show_error("Object doesn't have primary key or ID");
        }
        return self::$adapter->where($this->getIdField(), $id)->update($this->table, $this->attributes);
    }

    /**
     * 
     * @return int
     */
    public function trash()
    {
        if (!($id = $this->getId())) {
            show_error("Doesn't have a unique ID");
        }
        $result = self::$adapter
            ->from($this->table)
            ->where($this->getIdField(), $this->getId())
            ->delete();
        if ($result) {
            $this->isTrashed = true;
        }
        return $result;
    }

    /**
     * 
     * @param array $data
     * @return \Flare\Db\Sql\Model
     */
    public static function create(array $data = array())
    {
        return new static($data, true);
    }

    /**
     * 
     * @param int $limit
     * @param int $page
     * @return \Flare\Db\Sql\Model\Collection
     */
    public static function all($limit = null, $page = null)
    {
        $sql = with(new static)->query();
        if ($limit !== null) {
            $sql->limit($limit);
        }
        if ($page !== null) {
            $sql->page($page);
        }
        return $sql->getCollection();
    }

    /**
     * 
     * @param string|array $val
     * @param string $field
     * @return \Flare\Db\Sql\Model\Collection
     */
    public static function find($val, $field = null)
    {
        $instance = new static;
        if (!$field) {
            $field = $instance->getIdField();
        }
        $query = $instance->query();
        if (is_array($val)) {
            $query->whereIn($field, $val);
        } else {
            $query->where($field, $val);
        }
        return $query->getCollection();
    }

    /**
     * 
     * @param string|array $id
     * @param string $field
     * @return \Flare\Db\Sql\Model\Query
     */
    public static function filter($id, $field = null)
    {
        $instance = new static;
        if (!$field) {
            $field = $instance->getIdField();
            if (!$field) {
                show_error('No primary key / ID for '.$instance->getClass());
            }
        }

        $query = $instance->query();
        if (is_array($id)) {
            return $query->whereIn($field, $id);
        }
        return $query->where($field, $id);
    }

    /**
     * 
     * @param string|int $val
     * @param string $field
     * @return \Flare\Db\Sql\Model
     */
    public static function findOne($val, $field = null)
    {
        $instance = new static;
        if (!$field) {
            $field = $instance->getIdField();
        }
        return $instance->query()->where($field, $val)->getOne();
    }

    /**
     * 
     * @param string $key
     * @param mixed $value
     * @return \Flare\Db\Sql\Model
     */
    public function setAttribute($key, $value)
    {
        if ($this->new || $key !== $this->idField) {
            $this->attributes[$key] = $value;
        }
        return $this;
    }

    /**
     * 
     * @param array $attributes
     * @return \Flare\Db\Sql\Model
     */
    public function setAttributes(array $attributes)
    {
        if (!$this->new && $this->idField) {
            $attributes[$this->idField] = $this->getId();
        }
        $this->attributes = $attributes;
        return $this;
    }

    /**
     * 
     * @param string $key
     * @return mixed
     */
    public function getAttribute($key)
    {
        if (!isset($this->attributes[$key])) {
            return null;
        }
        return $this->attributes[$key];
    }

    /**
     * 
     * @return array
     */
    public function toArray()
    {
        return $this->attributes;
    }

    /**
     * 
     * @return string
     */
    public function toJSON()
    {
        return json_encode($this->attributes);
    }

    /**
     * 
     * @param string $value
     * @return \Flare\Db\Sql\Model
     */
    public function setId($value)
    {
        if (!$this->idField) {
            show_error("No primary key / ID field was set");
        }
        return $this->setAttribute($this->idField, $value);
    }

    /**
     * 
     * @return \Flare\Db\Sql\Model
     */
    public function setIdField($field)
    {
        $this->idField = $field;
        return $this;
    }

    /**
     * 
     * @return string
     */
    public function getIdField()
    {
        return $this->idField;
    }

    /**
     * 
     * @return string|int
     */
    public function getId()
    {
        if (!$this->idField) {
            return null;
        }
        return $this->getAttribute($this->idField);
    }

    /**
     * 
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function __set($key, $value)
    {
        $this->setAttribute($key, $value);
    }

    /**
     * 
     * @param string $key
     * @return boolean
     */
    public function __isset($key)
    {
        return isset($this->attributes[$key]);
    }

    /**
     * 
     * @param string $key
     * @return void
     */
    public function __unset($key)
    {
        unset($this->attributes[$key]);
    }

    /**
     * 
     * @return \Flare\Db\Sql\Driver
     */
    public function & getAdapter()
    {
        return self::$adapter;
    }

    /**
     * 
     * @param array $data
     * @return \Flare\Db\Sql\Model
     */
    public static function insert(array $data)
    {
        $instance = self::create($data);
        if (!$instance->save()) {
            return null;
        }
        return $instance->setAsNew(false);
    }

    /**
     * 
     * @param string $key
     * @return mixed
     */
    public function __get($key)
    {
        return $this->getAttribute($key);
    }

    /**
     * 
     * @return \Flare\Db\Sql\Model\Query
     */
    public function query()
    {
        return new Query($this);
    }

    /**
     * 
     * @return \Flare\Db\Sql\Model\Query
     */
    public static function createQuery()
    {
        return with(new static)->query();
    }

    /**
     * 
     * @return void
     */
    public function __clone()
    {
        $this->isObject = true;
    }

    /**
     * 
     * @return boolean
     */
    public function isTrashed()
    {
        return $this->isTrashed;
    }

    /**
     * 
     * @param string $method
     * @param array $args
     * @return mixed
     */
    public function __call($method, $args)
    {
        if ($this->isTrashed) {
            show_error('Object was already trashed');
        } elseif (strpos($method, '_') === 0) {
            show_error("Can't access private method");
        } elseif (method_exists($this, $method)) {
            return call_user_func_array(array($this, $method), $args);
        } elseif ($this->isObject) {
            show_error("Undefined method '{$method}'");
        }
        return call_user_func_array(array($this->query(), $method), $args);
    }

    /**
     * 
     * @param string $method
     * @param array $args
     * @return mixed
     */
    public static function __callStatic($method, $args)
    {
        if (strpos($method, '_') === 0) {
            show_error("Can't call private method");
        }
        return call_user_func_array(array(new static, $method), $args);
    }
}