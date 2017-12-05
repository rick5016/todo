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

    function isChecked()
    {
        return $this->checked;
    }
    
    function getCheckedHTML()
    {
        if ($this->checked) {
            return ' checked';
        }
        
        return '';
    }
    
    function getHTML()
    {
        return '<input' . $this->getHTMLAttributs() . $this->getCheckedHTML() . ' type="checkbox" id="' . $this->name . '" name="' . $this->name . '" value="1" />';
    }
}