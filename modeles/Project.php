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
    
    function getTasks()
    {
        if (isset($this->tasks)) {
            return $this->tasks;
        }
        
        return null;
    }

    public function save()
    {
        try {
            if ($this->dbh->beginTransaction())
            {
                parent::save();
                if (isset($this->calandars))
                {
                    foreach ($this->calandars as $task)
                    {
                        $task->setIdProject((int) $this->getId());
                        $task->save();
                    }
                }
                $this->dbh->commit();
            }
        } catch (PDOException $e) {
            $this->dbh->rollBack();
            var_dump($e->getMessage().' At line '.$e->getLine());
            exit;
        }
    }

}
