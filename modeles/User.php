<?php

class ORM_User extends Model
{

    protected $bdd_name     = 'user';
    protected $attributs    = array(
        'id'       => 'id',
        'login'    => 'login',
        'password' => 'password',
        'active'   => 'active',
        'created'  => 'created',
        'updated'  => 'updated'
    );
    protected $primary_key  = 'id';
    protected $foreign_keys = array(
    );
    
    function __sleep()
    {
        unset($this->dbh);
    }

    function setUser()
    {
        $_SESSION['user'] = serialize($this);
    }
    
    function getUser()
    {
        if (isset($_SESSION['user'])) {
            return unserialize($_SESSION['user']);
        }
        
        throw new Exception('Aucun utilisateur trouvé');
    }
    
    function logout()
    {
        unset($_SESSION['user']);
    }
    
}
