<?php

class Repository_User
{

    function login()
    {
        $login = $request->getParam('login');
        $password = $request->getParam('password');
        
        if (isset($login) && $password)
        {
            $_SESSION['user'] = $this->loadOne(false, array('login' => $login, 'password' => $password));
        }
        
        return false;
    }

    function logout()
    {
        unset($_SESSION['user']);
    }
}
