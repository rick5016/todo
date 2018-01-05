<?php

class ORM_Performe extends Model
{
    
    protected $bdd_name  = 'performe';
    protected $attributs = array(
        'id'        => 'id',
        'idTask'    => 'idTask',
        'created'   => 'created',
        'updated'   => 'updated'
    );
    protected $primary_key = 'id';
    protected $foreign_keys = array(
        'task' => array('task','id','idTask')
    );

}
