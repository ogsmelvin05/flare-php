<?php

namespace Flare\View\UI;

use Flare\Security\Xss;
use Flare\View\UI;

/**
 * 
 * @author anthony
 * 
 */
class Select extends UI
{
    /**
     * 
     * @var array
     */
    private $_options;

    /**
     * 
     * @var string
     */
    private $_textKey;

    /**
     * 
     * @var string
     */
    private $_valueKey;

    /**
     * 
     * @param array $options
     * @param string $textKey
     * @param string $optionValueKey
     * @param string $placeholder
     * @param string|int $selected
     */
    public function __construct($options, $textKey = null, $optionValueKey = null, $placeholder = null, $selected = null)
    {
        $this->_options = $options;
        if ($textKey) $this->setTextKey($textKey);
        if ($optionValueKey) $this->setValueKey($optionValueKey);
        if ($placeholder) $this->setPlaceholder($placeholder);
        if ($selected) $this->setSelected($selected);
    }

    /**
     * 
     * @param string $selected
     * @return \Flare\View\UI\Select
     */
    public function setSelected($selected)
    {
        $this->_selected = $selected;
        return $this;
    }

    /**
     * 
     * @param string $textKey
     * @return \Flare\View\UI\Select
     */
    public function setTextKey($textKey)
    {
        $this->_textKey = $textKey;
        return $this;
    }

    /**
     * 
     * @param string $textKey
     * @return \Flare\View\UI\Select
     */
    public function setValueKey($valueKey)
    {
        $this->_valueKey = $valueKey;
        return $this;
    }

    /**
     * 
     * @return string
     */
    public function render()
    {
        $list = '';
        if ($this->_placeholder) {
            $list .= '<option value="">'.$this->_placeholder.'</option>';
        }
        foreach ($this->_options as $value) {
            $key = $value;
            if ($this->_valueKey) {
                $key = isset($value[$this->_valueKey]) ? $value[$this->_valueKey] : '';
            }
            if ($this->_textKey) {
                $value = isset($value[$this->_textKey]) ? $value[$this->_textKey] : '';
            }
            $list .= '<option value="'.Xss::filter($key).'"';
            if ($this->_selected !== null && $key == $this->_selected) {
                $list .= ' selected="selected"';
            }
            $list .= '>'.Xss::filter($value).'</option>';
        }
        $html = "<select";
        if ($this->_name) {
            $html .= ' name="'.$this->_name.'"';
        }
        if ($this->_classes) {
            $html .= ' class="'.implode(' ', $this->_classes).'"';
        }
        if ($this->_style) {
            $html .= " style=\"";
            foreach ($this->_style as $key => $val) {
                $html .= "{$key}: {$val};";
            }
            $html .= "\"";
        }
        if ($this->_id) {
            $html .= " id=\"{$this->_id}\"";
        }
        $html .= ">{$list}</select>";
        return $html;
    }
}