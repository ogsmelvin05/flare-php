<?php

namespace Flare\Object;

use Flare\Util\Collection;
use Flare\Object;
use ArrayObject;
use ArrayAccess;
use Iterator;

/**
 * 
 * @author anthony
 * 
 */
class Json extends Object implements ArrayAccess, Iterator
{
    /**
     * 
     * @param string|array $data
     * @param boolean $isPathOrUrl
     */
    public function __construct($data = array(), $isPathOrUrl = false)
    {
        if (is_string($data)) {
            if ($isPathOrUrl) {
                $data = @file_get_contents($data);
                if ($data === false) {
                    show_error("Error encountered accessing JSON URL");
                }
            }
            $data = json_decode($data, true);
            if (!is_array($data)) {
                show_error("Invalid JSON Format");
            }
        } elseif ($data instanceof Collection) {
            $data = $data->toArray();
        } elseif ($data instanceof ArrayObject) {
            $data = $data->getArrayCopy();
        }

        $this->_data = $data;
    }

    /**
     * 
     * @return mixed
     */
    public function rewind()
    {
        return reset($this->_data);
    }

    /**
     * 
     * @return mixed
     */
    public function current()
    {
        return current($this->_data);
    }

    /**
     * 
     * @return mixed
     */
    public function key()
    {
        return key($this->_data);
    }

    /**
     * 
     * @return mixed
     */
    public function next()
    {
        return next($this->_data);
    }

    /**
     * 
     * @return boolean
     */
    public function valid()
    {
        return key($this->_data) !== null;
    }

    /**
     * 
     * @param mixed $offset
     * @return boolean
     */
    public function offsetExists($offset)
    {
        return isset($this->_data[$offset]);
    }

    /**
     * 
     * @param mixed $offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        if (!isset($this->_data[$offset])) {
            show_error("Undefined '{$offset}' key in JSON Object");
        }
        return $this->_data[$offset];
    }

    /**
     * 
     * @param mixed $offset
     * @param mixed $value
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        if ($offset === null) {
            $this->_data[] = $value;
        } else {
            $this->_data[$offset] = $value;
        }
    }

    /**
     * 
     * @param mixed $offset
     * @return void
     */
    public function offsetUnset($offset)
    {
        unset($this->_data[$offset]);
    }

    /**
     * 
     * @return string
     */
    public function encode()
    {
        return json_encode($this->_data);
    }

    /**
     * 
     * @return string
     */
    public function __toString()
    {
        return $this->encode();
    }
}