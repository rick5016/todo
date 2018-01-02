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

}
