<?php

class ORM_Ia extends Model
{

    protected $bdd_name     = 'ia';
    protected $attributs    = array(
        'id'   => 'id',
        'mot'  => 'name',
        'classe' => 'type',
        'idb'  => 'idb',
        'com'  => 'com'
    );
    protected $primary_key  = 'id';
    protected $foreign_keys = array(
    );

}
