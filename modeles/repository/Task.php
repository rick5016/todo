<?php

class Repository_Task extends ORM_Task
{
    
    function loadInbox($request)
    {
        $priority  = $request->getParam('priority', '11111');
        $filtre    = $request->getParam('filtre');
        $result    = array();
        if ($priority == "00000") {
            return $result;
        }
        
        try {
            $query = 'select 
                project.id as project_id, project.name as project_name, project.color as project_color, project.created as project_created, project.updated as project_updated, 
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
                where active = 1 and (reiterate != 0 OR (reiterate = 0 AND performe.id is null))';
            
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
            
            if ((!isset($_SESSION['date_future']) || !$_SESSION['date_future']) || (isset($filtre) && $filtre == 'today')) {
                $query .= ' and date(dateStart) <= date(now())'; // N'affiche pas les dates dans le futur
            }
            if ((!isset($_SESSION['date_passee']) || !$_SESSION['date_passee']) || (isset($filtre) && $filtre == 'today')) {
                $query .= ' and (date(dateStart) >= date(now()) or date(dateEnd) >= date(now()) or reiterate != 0)'; // N'affiche pas les dates dans le passé (sauf si elles se répètent)
            }
            
            if (isset($_SESSION['project']) && is_array($_SESSION['project']) && count($_SESSION['project']) > 0) {
                $query .= " and project.id IN (" . implode(', ', $_SESSION['project']) . ')';
            } else if (isset($_SESSION['projectdel']) && is_array($_SESSION['projectdel']) && count($_SESSION['projectdel']) > 0) {
                $query .= " and project.id NOT IN (" . implode(', ', $_SESSION['projectdel']) . ')'; 
            } else {
                $query .= " and project.id IN ('')";
            }
            
            $query .= ' order by dateStart, priority, performe_id desc';

            $stmt  = $this->dbh->query($query);
            if ($stmt)
            {
                while ($data = $stmt->fetch()) {
                    $result[] = new ORM_Task($data, true);
                }
            }
        }
        catch (PDOException $e) {
            var_dump($e->getMessage().' At line '.$e->getLine());
            exit;
        }
        return $result;
    }
    
    function performer($request)
    {
        $id = $request->getParam('id');
        
        if (isset($id))
        {
            $task = $this->loadOne(false, array('id' => $id));
            if (isset($task)) {
                $task->performe();
                return true;
            } 
        }
        
        return false;
    }
    
    /*
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
    
    function setDateStartOrigine($dateStart)
    {
        if (strpos($dateStart, '/'))
        {
            $arrayDateTime   = explode(' ', $dateStart); // Time
            $arrayDate       = explode('/', $arrayDateTime[0]);
            if (isset($arrayDateTime[1])) {
                $this->dateStartOrigine = $arrayDate[2] . '-' . $arrayDate[1] . '-' . $arrayDate[0] . ' ' . $arrayDateTime[1];
            } else {
                $this->dateStartOrigine = $arrayDate[2] . '-' . $arrayDate[1] . '-' . $arrayDate[0] . " 00:00:00";
            }
        }
        else {
            $this->dateStartOrigine = $dateStart;
        }
    }

    function setDateEndOrigine($dateEnd)
    {
        if (strpos($dateEnd, '/'))
        {
            $arrayDateTime   = explode(' ', $dateEnd); // Time
            $arrayDate       = explode('/', $arrayDateTime[0]);
            if (isset($arrayDateTime[1])) {
                $this->dateEndOrigine = $arrayDate[2] . '-' . $arrayDate[1] . '-' . $arrayDate[0] . ' ' . $arrayDateTime[1];
            } else {
                $this->dateEndOrigine = $arrayDate[2] . '-' . $arrayDate[1] . '-' . $arrayDate[0] . " 00:00:00";
            }
        }
        else {
            $this->dateEndOrigine = $dateEnd;
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
    */
    
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
                $task = Model::factory("task")->loadOne(false, array('id' => $id));
                $dateTimeStart  = new DateTime($task->dateStart);
                $dateTimeEnd  = new DateTime($task->dateEnd);

                // Calcul de l'interval de jour entre la date de début et la date de fin
                $diff = $dateTimeStart->diff($dateTimeEnd);
                $interval = $diff->format('%a');
                
                // Calcul de la date de début
                $dateTimeStart->sub(new DateInterval('P' . $task->getInterspace() . $task->getReiterateLetter()));
                $task->setDateStart($dateTimeStart->format('Y-m-d H:i'));

                // Calcul de la date de fin
                $dateTimeStart->add(new DateInterval('P' . $interval . 'D'));
                $task->setDateEnd($dateTimeStart->format('Y-m-d') . ' ' . $dateTimeEnd->format('H:i'));
                $task->save();
                Model::factory('performe')->delete(false, $idPerforme);
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
    
    public function saveTask($params, $task)
    {
        $project_id     = $params['project'];
        $task_name      = $params['task_name'];
        $priority       = isset($params['priority']) ? $params['priority'] : 0;
        $dateStartSave  = $this->dateFormat($params['dateStart']);
        $dateEndSave    = $this->dateFormat($params['dateEnd']);
        $repeat         = $params['repeat'];
        $interspace     = empty($params['interspace']) ? null : $params['interspace'];
        $reiterateEnd   = $params['reiterateEnd'];
        if (!empty($params['untilDate'])) {
            $untilDate      = $params['untilDate'];
        }
        $untilNumber    = $params['untilNumber'];

        if (isset($params['timeStart']) && !empty($params['timeStart'])) {
            $dateStartSave .= ' ' . $params['timeStart'] . ':00';
        } else {
            $dateStartSave .= ' 00:00:00';
        }
        if (isset($params['timeEnd']) && !empty($params['timeEnd'])) {
            $dateEndSave .= ' ' . $params['timeEnd'] . ':00';
        } else {
            $dateEndSave .= ' 00:00:00';
        }

        $task->setDateStart($dateStartSave);
        $task->setDateEnd($dateEndSave);
        $task->setDateStartOrigine($dateStartSave);
        $task->setDateEndOrigine($dateEndSave);
        $task->reiterate    = $repeat;
        $task->interspace   = $interspace;
        $task->reiterateEnd = $reiterateEnd;
        $task->untilNumber  = $untilNumber;
        $task->name         = $task_name;
        $task->priority     = $priority;
        $task->idProject    = $project_id;
        if (isset($untilDate)) {
            $task->untilDate    = $untilDate;
        }
        
        return $task->save();
    }

    public function dateFormat($date)
    {
        if (strpos($date, '/'))
        {
            $arrayDateTime   = explode(' ', $date); // Time
            $arrayDate       = explode('/', $arrayDateTime[0]);
            if (isset($arrayDateTime[1])){
                return $arrayDate[2] . '-' . $arrayDate[1] . '-' . $arrayDate[0] . ' ' . $arrayDateTime[1];
            }
            else {
                return $arrayDate[2] . '-' . $arrayDate[1] . '-' . $arrayDate[0];
            }
        }
        return $date;
    }
}
