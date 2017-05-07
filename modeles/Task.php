<?php

include_once 'modeles/BDD.php';
class Task extends BDD
{

    protected $bdd_name  = 'task';
    protected $attributs = array(
        'id'                    => 'id',
        'idProject'             => 'idProject',
        'name'                  => 'name',
        'priority'              => 'priority',
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
        'project' => array('project','idProject', 'id'),
        'performes' => array('performe','id', 'idTask')
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
    
    function loadInbox($priority = "11111")
    {
        $result = array();
        if ($priority == "00000") {
            return $result;
        }
        try {
            $query = 'select 
                project.id as project_id, project.name as project_name, project.created as project_created, project.updated as project_updated, 
                task.id as task_id, task.name as task_name, idProject, priority, dateStart, dateEnd, reiterate, interspace, reiterateEnd, untilDate, untilNumber, task.created as task_created, task.updated as task_updated, 
                performe.id as performe_id, idTask, performe.created as performe_created, performe.updated as performe_updated
                from project
                left join task on project.id = task.idProject
                left join performe on task.id = performe.idTask and performe.created = 
                (
                        SELECT
                    MAX(created)
                    FROM performe
                    where performe.idTask = task.id
                    limit 1
                )
                where (reiterate != 0 OR (reiterate = 0 AND performe.id is null))';
            if ($priority != "11111")
            {
                $query .= " and priority IN (";
                if (substr($priority, 0, 1) == '1') {
                    $query .= '0,';
                }
                if (substr($priority, 1, 1) == '1') {
                    $query .= '1,';
                }
                if (substr($priority, 2, 1) == '1') {
                    $query .= '2,';
                }
                if (substr($priority, 3, 1) == '1') {
                    $query .= '3,';
                }
                if (substr($priority, 4, 1) == '1') {
                    $query .= '4,';
                }
                $query = substr($query, 0, -1);
                $query .= ')';
            }
            $query .= ' order by dateStart, priority, performe_id desc';

            $stmt  = $this->dbh->query($query);
            if ($stmt)
            {
                while ($data = $stmt->fetch()) {
                    $result[] = new $this->bdd_name($data, true);
                }
            }
        }
        catch (PDOException $e) {
            var_dump($e->getMessage().' At line '.$e->getLine());
            exit;
        }
        return $result;
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
                    
                    // Calcul de l'interval
                    $diff = $dateTimeStart->diff($dateTimeEnd);
                    $interval = $diff->format('%a');
                    
                    // Calcul de la date de début
                    $newDateTimeStart = new DateTime($this->getNewDate($this->dateStart, $this->reiterate, $this->interspace));
                    $this->setDateStart($newDateTimeStart->format('Y-m-d H:i'));
                    
                    // Calcul de la date de fin
                    $newDateTimeStart->add(new DateInterval('P' . $interval . 'D'));
                    $this->setDateEnd($newDateTimeStart->format('Y-m-d') . ' ' . $dateTimeEnd->format('H:i'));

                    parent::save();
                }
                $performe = new Performe(array('idTask' => $this->id));
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
    function getNewDate($dateParam, $reiterate, $interval, $search = 'next')
    {
        $dateTimeOrigine    = new DateTime($dateParam);
        $date               = $dateTimeOrigine->format('Y-m-d');
        $lastDate           = $date;
        $today              = date('Y-m-d');
        
        while ($date <= $today)
        {
            $lastDate = $date;
            
            // TODO : la date de début d'une ité"ration au mois doit avoir le même jour que la date d'origine, sinon, on passe au mois suivant
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
            return $date . ' '. $dateTimeOrigine->format('H:i') . ':00';
        } else {
            return $lastDate . ' ' . $dateTimeOrigine->format('H:i') . ':00';
        }
    }
    
    function deleteLastPerforme($id, $idPerforme)
    {
        try {
            if ($this->dbh->beginTransaction())
            {
                $task = BDD::factory("task")->loadOne(false, array('id' => $id));
                $dateTimeStart  = new DateTime($task->dateStart);
                $dateTimeEnd  = new DateTime($task->dateEnd);

                // Calcul de l'interval
                $diff = $dateTimeStart->diff($dateTimeEnd);
                $interval = $diff->format('%a');
                
                // Calcul de la date de début
                // TODO : gestion des semaines/mois/annees
                $dateTimeStart->sub(new DateInterval('P' . $task->interspace . 'D'));
                $task->setDateStart($dateTimeStart->format('Y-m-d H:i'));

                // Calcul de la date de fin
                $dateTimeStart->add(new DateInterval('P' . $interval . 'D'));
                $task->setDateEnd($dateTimeStart->format('Y-m-d') . ' ' . $dateTimeEnd->format('H:i'));
                $task->save();
                BDD::factory('performe')->delete(false, $idPerforme);
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

}
