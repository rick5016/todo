<?php

class form
{
    public $elements = array();
    
    function add($type, $name)
    {
        $this->elements[] = array($type, $name);
    }
    
}
