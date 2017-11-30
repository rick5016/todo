<?php

class Element_Submit extends Form_Element
{
    
    function getHTML()
    {
        return '<input type="submit" name="' . $this->name . '" value="' . $this->value . '" />';
    }
}
