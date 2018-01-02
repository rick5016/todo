<?php

class Form_register extends form
{
    
    function __construct()
    {
        $this->add($username = new Element_Text('username'));
        $username->setRequired(true);
        $username->setLibelle('Login');
        $username->setAttributs(array('tabindex' => '1', 'class' => 'form-control', 'placeholder' => 'Login'));
        
        $this->add($email = new Element_Text('email'));
        $email->setRequired(true);
        $email->setLibelle('Email Address');
        $email->setAttributs(array('tabindex' => '1', 'class' => 'form-control', 'placeholder' => 'Email Address'));
        
        $this->add($password = new Element_Password('password'));
        $password->setRequired(true);
        $password->setLibelle('Password');
        $password->setAttributs(array('tabindex' => '2', 'class' => 'form-control', 'placeholder' => 'Password'));
        
        $this->add($confirmPassword = new Element_Password('confirmPassword'));
        $confirmPassword->setRequired(true);
        $password->setLibelle('Confirm Password');
        $confirmPassword->setAttributs(array('tabindex' => '2', 'class' => 'form-control', 'placeholder' => 'Confirm Password'));
        
        $this->add($submit = new Element_submit('register-submit'));
        $submit->setAttributs(array('tabindex' => '4', 'class' => 'form-control btn btn-register'));
        $submit->setValue('Register Now');
        $submit->setLibelle('Submit');
    }
    
    function isValid($params)
    {
        if (parent::isValid($params)) { 
            return $this->loadOne(false, array('login' => $params['login'], 'password' => $params['password']));
        }
        
        return false;
    }
}
