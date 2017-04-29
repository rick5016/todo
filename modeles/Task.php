<?php

include_once 'modeles/BDD.php';
class Task extends BDD
{

    protected $bdd_name  = 'task';
    protected $attributs = array(
        'id'        => 'id',
        'name'      => 'name',
        'priority'  => 'priority'
    );
    protected $primary_key = 'id';
    protected $foreign_keys = array(
        'calendars' => array('calendar','id','idtask')
    );
    
    public $calendar = array();
    
    function __clone()
    {
        $obj            = new Task();
        $obj->id        = $this->id;
        $obj->name      = $this->name;
        $obj->priority  = $this->priority;
        $obj->calendars = $this->calendars;

        return $obj;
    }

    public function save()
    {
        try {
            if ($this->dbh->beginTransaction())
            {
                parent::save();
                foreach ($this->calandar as $calendar)
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
    
    function loadInbox($foreing_keys = false, $where = array(), $orderby = '')
    {
        try {
            $query = 'select 
                task.id as task_id, name, priority, 
                calendar.id as calendar_id, idtask, dateCreateCalendar, dateStart, dateEnd, reiterate, interspace, reiterateEnd, untilDate, untilNumber, 
                performe.id as performe_id, idcalendar, dateCreate, dateUpdate 
                from task
                left join calendar on task.id = calendar.idtask
                left join performe on calendar.id = performe.idcalendar and performe.dateCreate = 
                (
                        SELECT
                    MAX(dateCreate)
                    FROM performe
                    where performe.idcalendar = calendar.id
                    limit 1
                )
                where (reiterate != 0 OR (reiterate = 0 AND performe.id is null)) 
                order by dateStart, priority, performe_id desc';
            echo $query;
            $stmt  = $this->dbh->query($query);
            if ($stmt)
            {
                while ($data = $stmt->fetch()) {
                    $result[] = new $this->bdd_name($data, $foreing_keys);
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
