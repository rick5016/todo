<?php

include_once 'modeles/BDD.php';
class Calendar extends BDD
{

    protected $bdd_name  = 'calendar';
    protected $attributs = array(
        'id'                    => 'id',
        'idtask'                => 'idtask',
        'dateStart'             => 'dateStart',
        'dateEnd'               => 'dateEnd',
        'reiterate'             => 'reiterate', // Tous les
        'interspace'            => 'interspace', // intervalle entre les itérations
        'reiterateEnd'          => 'reiterateEnd', // Jusqu'à (toujours/custom)
        'untilDate'             => 'untilDate', // Jusqu'à une date
        'untilNumber'           => 'untilNumber', // Jusqu'à un nombre de fois
        'created'               => 'created',
        'updated'               => 'updated'
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
                $this->dateStart = $arrayDate[2] . '-' . $arrayDate[1] . '-' . $arrayDate[0] . ' ' . $arrayDateTime[1];
            } else {
                $this->dateStart = $arrayDate[2] . '-' . $arrayDate[1] . '-' . $arrayDate[0] . " 00:00:00";
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
                $this->dateEnd = $arrayDate[2] . '-' . $arrayDate[1] . '-' . $arrayDate[0] . ' ' . $arrayDateTime[1];
            } else {
                $this->dateEnd = $arrayDate[2] . '-' . $arrayDate[1] . '-' . $arrayDate[0] . " 00:00:00";
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
    
    function getPerformes()
    {
        if (isset($this->performes)) {
            return $this->performes;
        }
        
        return null;
    }
    
    function performe()
    {
        try {
            if ($this->dbh->beginTransaction())
            {
                $interval = $this->interspace;
                
                // Si la date de fin de l'événement est avant la date d'aujourd'hui alors on calcul la date de fin après aujourdh'ui
                if ($this->dateEnd < date('Y-m-d H:i:s')) {
                    $this->dateEnd = $this->getNextDateEnd($this->dateEnd, $this->reiterate, $interval);
                }
                
                // La date de début du prochain événement est la date le lendemain de la date de fin
                $dateTimeStart = new DateTime($this->dateStart);
                $dateTimeEnd = new DateTime($this->dateEnd);
                $dateTimeEnd->add(new DateInterval('P1D'));
                
                // Calcul de la nouvelle date de fin
                $dateTime = new DateTime($this->dateStart);
                if ($this->reiterate == 1) {
                    $dateTime->add(new DateInterval('P' . $interval . 'D'));
                } elseif ($this->reiterate == 2)
                {
                    $interval *= 7;
                    $dateTime->add(new DateInterval('P' . $interval . 'D'));
                } 
                elseif ($this->reiterate == 3)
                {
                    for ($i = 0;$i<$interval;$i++) {
                        $dateTime->modify('last day of next month');
                    }
                }
                elseif ($this->reiterate == 4)
                {
                    for ($i = 0;$i<$interval;$i++) {
                        $dateTime->modify('last day of next year');
                    }
                }
                $this->setDateStart($dateTimeEnd->format('Y-m-d') . ' ' . $dateTimeStart->format('H:i')); // On récup-re l'heure de la date de début
                $this->setDateEnd($dateTime->format('Y-m-d') . ' ' . $dateTimeEnd->format('H:i'));
                parent::save();
                
                $performe = new Performe(array('idcalendar' => $_GET['id']));
                $performe->save();
                
                $this->dbh->commit();
            }
        }
        catch (PDOException $e)
        {
            $this->dbh->rollBack();
            var_dump($e->getMessage().' At line '.$e->getLine());
            exit;
        }
    }
    
    function getNextDateEnd($date, $reiterate, $interval)
    {
        $today = date('Y-m-d');
        if ($date <= $today)
        {
            while ($date <= $today)
            {
                $dateTime = new DateTime($date);
                if ($reiterate == 1) {
                    $dateTime->add(new DateInterval('P' . $interval . 'D'));
                } elseif ($reiterate == 2)
                {
                    $interval *= 7;
                    $dateTime->add(new DateInterval('P' . $interval . 'D'));
                } 
                elseif ($reiterate == 3)
                {
                    for ($i = 0;$i<$interval;$i++) {
                        $dateTime->modify('last day of next month');
                    }
                }
                elseif ($reiterate == 4)
                {
                    for ($i = 0;$i<$interval;$i++) {
                        $dateTime->modify('last day of next year');
                    }
                }
                $date = $dateTime->format('Y-m-d');
            }
        }
        return $date;
    }

}
