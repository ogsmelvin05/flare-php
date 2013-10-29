<?php

namespace Flare\View;

/**
 * 
 * @author anthony
 * 
 */
abstract class UI
{
    /**
     * 
     * @var string
     */
    protected $_id;

    /**
     * 
     * @var string
     */
    protected $_name;

    /**
     * 
     * @var array
     */
    protected $_classes = array();

    /**
     * 
     * @var array
     */
    protected $_style = array();

    /**
     * 
     * @var string
     */
    protected $_placeholder;

    /**
     * 
     * @param string $class
     * @return \Flare\View\UI
     */
    public function addClass($class)
    {
        if (!in_array($class, $this->_classes)) {
            $this->_classes[] = strtolower($class);
        }
        return $this;
    }

    /**
     * 
     * @param string $name
     * @return \Flare\View\UI
     */
    public function setName($name)
    {
        $this->_name = $name;
        return $this;
    }

    /**
     * 
     * @param string $placeholder
     * @return \Flare\View\UI\Select
     */
    public function setPlaceholder($placeholder)
    {
        $this->_placeholder = $placeholder;
        return $this;
    }

    /**
     * 
     * @param string $id
     * @return \Flare\View\UI
     */
    public function setId($id)
    {
        $this->_id = $id;
        return $this;
    }

    /**
     * 
     * @param string $name
     * @param string $value
     * @return \Flare\View\UI
     */
    public function addStyle($name, $value)
    {
        $this->_style[$name] = $value;
        return $this;
    }

    /**
     * 
     * @return string
     */
    public function __toString()
    {
        return $this->render();
    }

    /**
     * 
     * @return string
     */
    abstract public function render();
}