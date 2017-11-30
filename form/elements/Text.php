<?php

class Element_Text extends Form_Element
{

    function getHTML()
    {
        return '<input type="text" id="' . $this->name . '" name="' . $this->name . '"' . $this->getHTMLAttributs() . ' value="' . $this->value . '"' . $this->getHTMLStyle() . ' />';
    }

}
