<?php

include_once 'modeles/BDD.php';
class Performe extends BDD
{

    protected $bdd_name  = 'performe';
    protected $attributs = array(
        'id'            => 'id',
        'idcalendar'    => 'idcalendar',
        'created'       => 'created',
        'updated'       => 'updated'
    );
    protected $primary_key = 'id';
    protected $foreign_keys = array(
        'calendar' => array('calendar','id','idcalendar')
    );
    
    function getDateStart($display = false)
    {
        if ($display)
        {
            $tab = explode('-', $this->dateStart);
            return $tab[1] . '/' . $tab[0] . '/' . $tab[2];
        }
        
        return $this->dateStart;
    }

}
