<?php

class Element_Password extends Form_Element
{

    function getHTML()
    {
        return '<input type="password" id="' . $this->name . '" name="' . $this->name . '"' . $this->getHTMLAttributs() . ' value="' . $this->value . '"' . $this->getHTMLStyle() . ' />';
    }

}
