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

    public function getElements()
    {
        return $this->elements;
    }

    function add($element)
    {
        $this->addElement($element);
    }
    
    function addElement($element)
    {
        $this->elements[$element->getName()] = $element;
    }

    function isValid($params)
    {
        if (!is_array($params)) {
            // Exception
        }
            
        foreach ($this->elements as $element)
        {
            if (isset($params[$element->getName()]))
            {
                if ($element instanceof Element_Checkbox && $params[$element->getName()] == '1') {
                    $element->setChecked(true);
                }
                
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
        
        foreach ($this->elements as $element)
        {
            if (!empty($element->error)) {
                return true;
            }
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
