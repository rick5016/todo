<?php

class Element_Checkbox extends Form_Element
{
    public $checked;
    
    public function __construct($name, $checked = false, $value = '', $style = array())
    {
        $this->checked = $checked;
        parent::__construct($name, $value, $style);
    }

    function setChecked($checked)
    {
        $this->checked = $checked;
    }
    
    function getHTML()
    {
        return '<input type="checkbox" id="' . $this->name . '" name="' . $this->name . '" value="1" />';
    }
}