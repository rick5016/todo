<?php

include_once 'modeles/BDD.php';
class Calendar extends BDD
{

    protected $bdd_name  = 'calendar';
    protected $attributs = array(
        'id'                    => 'id',
        'idtask'                => 'idtask',
        'dateCreateCalendar'    => 'dateCreate',
        'dateStart'             => 'dateStart',
        'dateEnd'               => 'dateEnd',
        'reiterate'             => 'reiterate', // Tous les
        'interspace'            => 'interspace', // intervalle entre les itérations
        'reiterateEnd'          => 'reiterateEnd', // Jusqu'à (toujours/custom)
        'untilDate'             => 'untilDate', // Jusqu'à une date
        'untilNumber'           => 'untilNumber' // Jusqu'à un nombre de fois
    );
    protected $primary_key = 'id';
    protected $foreign_keys = array(
        'task' => array('task','idtask', 'id'),
        'performes' => array('performe','id', 'idcalendar')
    );
    
    function setDateStart($dateStart)
    {
        if (strpos($dateStart, '/'))
        {
            $arrayDateTime   = explode(' ', $dateStart); // Time
            $arrayDate       = explode('/', $arrayDateTime[0]);
            if (isset($arrayDateTime[1])) {
                $this->dateStart = $arrayDate[2] . '-' . $arrayDate[0] . '-' . $arrayDate[1] . ' ' . $arrayDateTime[1];
            } else {
                $this->dateStart = $arrayDate[2] . '-' . $arrayDate[0] . '-' . $arrayDate[1] . " 00:00:00";
            }
        }
        else {
            $this->dateStart = $dateStart;
        }
    }

    function setDateEnd($dateEnd)
    {
        if (strpos($dateEnd, '/'))
        {
            $arrayDateTime   = explode(' ', $dateEnd); // Time
            $arrayDate       = explode('/', $arrayDateTime[0]);
            if (isset($arrayDateTime[1])) {
                $this->dateEnd = $arrayDate[2] . '-' . $arrayDate[0] . '-' . $arrayDate[1] . ' ' . $arrayDateTime[1];
            } else {
                $this->dateEnd = $arrayDate[2] . '-' . $arrayDate[0] . '-' . $arrayDate[1] . " 00:00:00";
            }
        }
        else {
            $this->dateEnd = $dateEnd;
        }
    }
    
    function getDateStart($display = false)
    {
        if ($display)
        {
            $tab = explode('-', $this->dateStart);
            return $tab[1] . '/' . $tab[0] . '/' . $tab[2];
        }
        
        return $this->dateStart;
    }

    function getDateEnd($display = false)
    {
        if ($display)
        {
            $tab = explode('/', $this->dateEnd);
            return $tab[1] . '/' . $tab[0] . '/' . $tab[2];
        }
        
        return $this->dateEnd;
    }
    
    /**
     * Pour twig
     * 
     * @return type
     */
    function getPerformes()
    {
        if (isset($this->performes)) {
            return $this->performes;
        }
        
        return null;
    }
    
    function updatePerforme()
    {
        try {
            if ($this->dbh->beginTransaction())
            {
                $this->setDateStart(date('Y-m-d'));
                parent::save();
                
                $performe = new Performe(array('idcalendar' => $_GET['id']));
                $performe->save();
                
                $this->dbh->commit();
            }
        } catch (PDOException $e) {
            $this->dbh->rollBack();
            var_dump($e->getMessage().' At line '.$e->getLine());
            exit;
        }
    }

}
