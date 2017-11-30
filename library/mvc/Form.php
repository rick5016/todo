<?php

class Form
{

    public $errors   = array();
    public $elements = array();

    public static function init()
    {
        Form_Autoloader::register();
    }

    public function get($name)
    {
        return $this->getElement($name);
    }

    public function getElement($name)
    {
        return $this->elements[$name];
    }

    function add($element)
    {
        $this->addElement($element);
    }
    
    function addElement($element)
    {
        $this->elements[$element->getName()] = $element;
    }

    function isValid(array $params)
    {
        foreach ($this->elements as $element)
        {
            if (isset($params[$element->getName()])) {
                $element->setValue($params[$element->getName()]);
            }
            if (!$element->isValid()) {
                $this->errors[] = $element->error;
            }
        }

        if (empty($this->errors)) {
            return true;
        }
        
        return false;
    }
    
    function hasErrors()
    {
        if (!empty($this->errors)) {
            return true;
        }
        
        return false;
    }
    
    function getErrors()
    {
        $errors = array();
        foreach ($this->elements as $element)
        {
            if (!empty($element->error)) {
                $errors[] = $element->error;
            }
        }
        
        return $errors;
    }

}
