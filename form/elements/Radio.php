<?php

class Element_Radio extends Form_Element
{
    public $values;
    public $styles;
    
    public function __construct($name, array $values = array(), $value = '')
    {
        $this->values = $values;
        parent::__construct($name, $value);
    }
    
    function setValues($values)
    {
        $this->values = $values;
    }
    
    function setStyles(array $styles)
    {
        $this->styles = $styles;
    }
    function getHTMLStyles($key)
    {
        $css = '';
        foreach ($this->styles[$key] as $key => $value) {
            $css .= $key . ':' . $value . ';';
        }
        if (!empty($css)) {
            return ' style="' . $css . '"';
        }
        
        return '';
    }
    
    function getHTML()
    {
        $html = '<div' . $this->getHTMLStyle() . '>';
        $value      = 0;
        foreach ($this->values as $value => $libelle)
        {
            $checked = '';
            if (isset($this->value) && $this->value == $value) {
                $checked = ' checked="checked"';
            }
            $html .= '<input type="radio"' . $checked . 'name="' . $this->name . '" value="' . $value . '" /><span' . $this->getHTMLStyles($value) . '> '. $libelle .' </span>';
            $value++;
        }
        $html .= '</div>';
        
        return $html;
    }
}
