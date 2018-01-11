<?php

class Repository_Project
{
    
    public function load()
    {
        return Project::factory('project')->load();
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
