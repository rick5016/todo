<?php

class Element_Select extends Form_Element
{
    public $options;
    
    public function __construct($name, array $options = array(), $value = '')
    {
        $this->options = $options;
        parent::__construct($name, $value);
    }
    
    function setOptions(array $options)
    {
        $this->options = $options;
    }
    
    function getHTML()
    {
        $html = '<select id="' . $this->name . '" name="' . $this->name . '"' . $this->getHTMLStyle() . ' >';
        $html .= $this->getOptionsHTML();
        $html .= '</select>';
        
        return $html;
    }
    
    function getOptionsHTML()
    {
        $html = '';
        foreach ($this->options as $valueOption => $libelleOption)
        {
            $selected = '';
            if (isset($this->value) && $this->value == $valueOption) {
                $selected = ' selected="selected"';
            }
            $html .= '<option value="' . $valueOption . '"' . $selected . '>' . $libelleOption . '</option>';
        }
        return $html;
    }
}