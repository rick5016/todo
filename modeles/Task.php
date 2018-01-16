<?php

class ORM_Task extends Model
{

    protected $bdd_name  = 'task';
    protected $attributs = array(
        'id'                    => 'id',
        'idProject'             => 'idProject',
        'name'                  => 'name',
        'priority'              => 'priority',
        'dateStart'             => 'dateStart',
        'dateEnd'               => 'dateEnd',
        'dateStartOrigine'      => 'dateStartOrigine',
        'dateEndOrigine'        => 'dateEndOrigine',
        'reiterate'             => 'reiterate', // Tous les 0 : non, 1 : jour, 2 : semaine, 3 : mois, 4 : année
        'interspace'            => 'interspace', // intervalle entre les itérations
        'reiterateEnd'          => 'reiterateEnd', // Jusqu'à (toujours/custom)
        'untilDate'             => 'untilDate', // Jusqu'à une date
        'untilNumber'           => 'untilNumber', // Jusqu'à un nombre de fois
        'created'               => 'created',
        'updated'               => 'updated'
    );
    protected $primary_key = 'id';
    protected $foreign_keys = array(
        'project' => array('project','idProject', 'id'),
        'performes' => array('performe','id', 'idTask'),
        'performed' => array('performe',array('id' => 'idTask', 'performe.created' => '(SELECT MAX(created) FROM performe where performe.idTask = task.id limit 1)'))
    );
    
    function getReiterateLetter()
    {
        if ($this->reiterate == 3) {
            return 'M';
        } elseif ($this->reiterate == 4) {
            return 'Y';
        }
        
        return 'D';
    }
    
//    function getInterspace()
//    {
//        if ($this->reiterate == 2) {
//            return $this->interspace*7;
//        }
//        
//        return $this->interspace;
//    }
    
    /**
     * Si la date d'aujoud'hui ce situe entre le début et la fin de l'événement alors la date d'affiche est la date d'aujourd'hui
     * Si la date de fin est passé et que retierate <> de 0 alors la date d'affiche est la date d'aujourd'hui
     */
    function setDateAffichage()
    {
        $this->dateAffichage = $this->dateStart;

        if (($this->dateStart < date('Y-m-d H:i') && $this->dateEnd >  date('Y-m-d H:i')) || ($this->dateEnd <  date('Y-m-d H:i') && $this->reiterate != 0)) {
            $this->dateAffichage = date('Y-m-d H:i:s');
        }
    }
    
    /**
     * TODO : A revoir
     */
    function setMoment()
    {
        $dateStart_dateTime = new DateTime($this->dateStart);
        $dateEnd_dateTime   = new DateTime($this->dateEnd);
        $timeStart          = $dateStart_dateTime->format('H:i');
        $timeEnd            = $dateEnd_dateTime->format('H:i');

        $this->moment = 3; // Toute la journée
        if ($timeStart != "00:00" || $timeEnd != "00:00")
        {
            if ($timeStart == "00:00" and $timeEnd == "11:59") {
                $this->moment = 1; // Matin
            } elseif ($timeStart == "12:00" && ($timeEnd == "17:59" || $timeEnd == "23:59")) {
                $this->moment = 2; // Après-midi et soir
            } elseif ($timeStart == "18:00" and $timeEnd == "23:59") {
                $this->moment = 4; // soir
            }
        }
    }
    
    /**
     * Compte le nombre de fois que la tâche a été effectuée
     * 
     * @return int $result
     */
    function setNbPerforme()
    {
        if ($this->reiterate == 0) {
            $this->nbPerforme = false;
        }
        
        $this->nbPerforme = 0;
        try {
            $query = "select count(*) as nbPerforme from performe where idTask = " . $this->id;

            $stmt  = $this->dbh->query($query);
            if ($stmt)
            {
                while($data = $stmt->fetch()) {
                    $this->nbPerforme = $data['nbPerforme'];
                }  
            }
        }
        catch (PDOException $e) {
            var_dump($e->getMessage().' At line '.$e->getLine());
            exit;
        }
    }
    
    function performe()
    {
        try {
            if ($this->dbh->beginTransaction())
            {
                if ($this->reiterate != 0)
                {
                    // Calcul de la prochaine date de début
                    // Exemple
                    // Today : 04/05/2017
                    // Event1 : Début : 01/05/2017 Fin : 01/05/2017 / Tous les jours   / Nouvelle date de début : 05/05/2017, Nouvelle date de fin : 05/05/2017
                    // Event2 : Début : 01/05/2017 Fin : 10/05/2017 / Tous les 7 jours / Nouvelle date de début : 08/05/2017, Nouvelle date de fin : 17/05/2017
                    // Event3 : Début : 04/05/2017 Fin : 04/05/2017 / Tous les jours   / Nouvelle date de début : 05/05/2017, Nouvelle date de fin : 05/05/2017
                    // Event4 : Début : 04/05/2017 Fin : 04/05/2017 / Tous les 2 jours / Nouvelle date de début : 06/05/2017, Nouvelle date de fin : 06/05/2017
                    // Event5 : Début : 04/05/2017 Fin : 05/05/2017 / Tous les jours   / Nouvelle date de début : 05/05/2017, Nouvelle date de fin : 06/05/2017
                    // Event6 : Début : 01/05/2017 Fin : 07/05/2017 / Tous les 7 jours / Nouvelle date de début : 08/05/2017, Nouvelle date de fin : 14/05/2017
                    // Event7 : Début : 01/05/2017 Fin : 01/05/2017 / Tous les 1 mois  / Nouvelle date de début : 01/06/2017, Nouvelle date de fin : 01/06/2017
                    // Event8 : Début : 01/05/2017 Fin : 31/05/2017 / Tous les 1 mois  / Nouvelle date de début : 01/06/2017, Nouvelle date de fin : 30/06/2017
                    
                    $dateTimeStart  = new DateTime($this->dateStart);
                    $dateTimeEnd  = new DateTime($this->dateEnd);
                    
                    // Calcul de l'interval pour la nouvelle date de fin
                    $diff = $dateTimeStart->diff($dateTimeEnd);
                    $interval = $diff->format('%a');
                    
                    // Calcul de la nouvelle date de début
                    $newDateTimeStart = new DateTime($this->getNewDate($this->dateStart, $this->reiterate, $this->interspace));
                    $this->setDateStart($newDateTimeStart->format('Y-m-d H:i:s'));
                    
                    // Calcul de la date de fin
                    $newDateTimeStart->add(new DateInterval('P' . $interval . 'D'));
                    $this->setDateEnd($newDateTimeStart->format('Y-m-d') . ' ' . $dateTimeEnd->format('H:i:s'));

                    parent::save();
                }
                $performe = new ORM_Performe(array('idTask' => $this->id));
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
    
    /**
     * Calcul de la prochaine date de début
     * 
     * @param string $dateParam
     * @param int $reiterate
     * @param int $interval
     * @return string type
     */
    function getNewDate($dateParam, $reiterate, $interval, $search = 'next', $dateCompare = null)
    {
        $dateTimeOrigine    = new DateTime($dateParam);
        $date               = $dateTimeOrigine->format('Y-m-d');
        $lastDate           = $date;
        if (!isset($dateCompare)) {
            $dateCompare = date('Y-m-d');
        }
        
        while ($date <= $dateCompare)
        {
            $lastDate = $date;
            
            // TODO : la date de début d'une itération au mois doit avoir le même jour que la date d'origine, sinon, on passe au mois suivant
            $dateTime = new DateTime($date);
            $reiterate_type = 'D';
            if ($reiterate == 2) { // Semaine
                $interval *= 7;
            } elseif ($reiterate == 3) {
                $reiterate_type = 'M';
            } elseif ($reiterate == 4) {
                $reiterate_type = 'Y';
            }
            
            $dateTime->add(new DateInterval('P' . $interval . $reiterate_type));
            
            $date = $dateTime->format('Y-m-d');
        }
        
        if ($search == 'next') {
            return $date . ' '. $dateTimeOrigine->format('H:i:s');
        } else {
            return $lastDate . ' ' . $dateTimeOrigine->format('H:i:s');
        }
    }

}
