<?php

class Form_filtres extends form
{
    
    function __construct()
    {
        $this->add($date_passee = new Element_Checkbox('date_passee'));
        $date_passee->setAttributs(array('data-toggle' => 'toggle', 'data-onstyle' => 'success', "data-offstyle" => 'danger'));
        
        $this->add($date_future = new Element_Checkbox('date_future'));
        $date_future->setAttributs(array('data-toggle' => 'toggle', 'data-onstyle' => 'success', "data-offstyle" => 'danger'));
        
        $this->add($details = new Element_Checkbox('details'));
        $details->setAttributs(array('data-toggle' => 'toggle', 'data-onstyle' => 'success', "data-offstyle" => 'danger'));
    }
    
    function isValid($request)
    {
        $valid = parent::isValid($request->getParams());
        if ($valid)
        {
            if ($request->isPost())
            {
                foreach ($this->getElements() as $name => $element)
                {
                    if ($this->getElement($name)->isChecked()) {
                        $_SESSION[$name] = true;
                    }
                    else
                    {
                        $this->getElement($name)->setChecked(false);
                        $_SESSION[$name] = false;
                    }
                }
            }
            else
            {
                if (!isset($_SESSION['date_passee']) && !isset($_SESSION['date_future']))
                {
                    $_SESSION['date_passee'] = true;
                    $_SESSION['date_future'] = true;
                }
                foreach ($this->getElements() as $name => $element)
                {
                    if (isset($_SESSION[$name]) && $_SESSION[$name]) {
                        $this->getElement($name)->setChecked(true);
                    }
                }
            }
        }
        
        return $valid;
    }
}
