<?php

class ORM_Project extends Model
{

    protected $bdd_name  = 'project';
    protected $attributs = array(
        'id'      => 'id',
        'name'    => 'name',
        'color'   => 'color',
        'active'  => 'active',
        'created' => 'created',
        'updated' => 'updated'
    );
    protected $primary_key = 'id';
    protected $foreign_keys = array(
        'tasks' => array('task','id','idProject')
    );
    
    function __toString()
    {
        return $this->name;
    }
}
