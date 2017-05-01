<?php

include_once 'modeles/BDD.php';
class Task extends BDD
{

    protected $bdd_name  = 'task';
    protected $attributs = array(
        'id'        => 'id',
        'name'      => 'name',
        'priority'  => 'priority',
        'created'   => 'created',
        'updated'   => 'updated'
    );
    protected $primary_key = 'id';
    protected $foreign_keys = array(
        'calendars' => array('calendar','id','idtask')
    );
    
    function getCalendars()
    {
        if (isset($this->calendars)) {
            return $this->calendars;
        }
        
        return null;
    }

    public function save()
    {
        try {
            if ($this->dbh->beginTransaction())
            {
                parent::save();
                foreach ($this->calandars as $calendar)
                {
                    $calendar->setIdtask((int) $this->getId());
                    $calendar->save();
                }
                $this->dbh->commit();
            }
        } catch (PDOException $e) {
            $this->dbh->rollBack();
            var_dump($e->getMessage().' At line '.$e->getLine());
            exit;
        }
    }
    
    function loadInbox($priority = "11111")
    {
        $result = array();
        if ($priority == "00000") {
            return $result;
        }
        try {
            $query = 'select 
                task.id as task_id, name, priority, task.created as task_created, task.updated as task_updated, 
                calendar.id as calendar_id, idtask, dateStart, dateEnd, reiterate, interspace, reiterateEnd, untilDate, untilNumber, calendar.created as calendar_created, calendar.updated as calendar_updated, 
                performe.id as performe_id, idcalendar, performe.created as performe_created, performe.updated as performe_updated
                from task
                left join calendar on task.id = calendar.idtask
                left join performe on calendar.id = performe.idcalendar and performe.created = 
                (
                        SELECT
                    MAX(created)
                    FROM performe
                    where performe.idcalendar = calendar.id
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

}
