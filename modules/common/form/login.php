<?php

class Form_login extends form
{
    
    function __construct()
    {
        $this->add($username = new Element_Text('username'));
        $username->setRequired(true);
        $username->setLibelle('Login');
        $username->setAttributs(array('tabindex' => '1', 'class' => 'form-control', 'placeholder' => 'Login'));
        
        $this->add($password = new Element_Password('password'));
        $password->setRequired(true);
        $password->setLibelle('Password');
        $password->setAttributs(array('tabindex' => '2', 'class' => 'form-control', 'placeholder' => 'Password'));
        
        $this->add($submit = new Element_submit('login-submit'));
        $submit->setAttributs(array('tabindex' => '4', 'class' => 'form-control btn btn-login'));
        $submit->setValue('Log in');
        $submit->setLibelle('Submit');
    }
    
    function isValid($params)
    {
        $isValid = parent::isValid($params);
        if ($isValid) {
            $user = Model::factory('user')->loadOne(false, array('login' => $params['username'], 'password' => $params['password']));
            if (!$user)
            {
                $this->getElement('username')->error = 'Utilisateur introuvable';
                return false;
            }
            $user->setUser();
            return true;
        }
        
        return false;
    }
}
