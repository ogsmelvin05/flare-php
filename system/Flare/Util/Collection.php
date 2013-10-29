<?php

namespace Flare\Util;

use ArrayObject;

/**
 * 
 * @author anthony
 * 
 */
class Collection extends ArrayObject
{
    /**
     * 
     * @param array $content
     * @param int $flag
     */
    public function __construct(array $content = array(), $flag = 0)
    {
        parent::__construct($content, $flag);
    }

    /**
     * 
     * @param callable|array|string $callback
     * @return \Flare\Util\Collection
     */
    public function each($callback)
    {
        if (is_callable($callback)) {
            foreach ($this as $key => &$row) {
                $callback($row, $key);
            }
        } elseif (is_array($callback) || is_string($callback)) {
            foreach ($this as $key => &$row) {
                call_user_func_array($callback, array($row, $key));
            }
        } else {
            show_error('Collection::each method parameter must be callable');
        }
        return $this;
    }

    /**
     * 
     * @return boolean
     */
    public function isEmpty()
    {
        return $this->count() ? false : true;
    }

    /**
     * 
     * @return mixed
     */
    public function last()
    {
        return end($this);
    }
    
    /**
     * 
     * @return mixed
     */
    public function first()
    {
        return reset($this);
    }

    /**
     * 
     * @param array|ArrayObject $array
     * @return \Flare\Util\Collection
     */
    public function merge($array)
    {
        if ($array instanceof ArrayObject) {
            $array = $array->getArrayCopy();
        }
        $this->exchangeArray(array_merge($this->getArrayCopy(), $array));
        return $this;
    }

    /**
     * 
     * @return string
     */
    public function toJSON()
    {
        return json_encode($this);
    }

    /**
     * 
     * @return array
     */
    public function toArray()
    {
        return $this->getArrayCopy();
    }

    /**
     * 
     * @return \Flare\Object\Json
     */
    public function toJSONObject()
    {
        return new Json($this->getArrayCopy());
    }
}