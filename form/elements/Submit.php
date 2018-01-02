<?php

class Element_Submit extends Form_Element
{
    
    function getHTML()
    {
        return '<input type="submit" id="' . $this->name . '" name="' . $this->name . '"' . $this->getHTMLAttributs() . ' value="' . $this->value . '"' . $this->getHTMLStyle() . ' />';
    }
}
