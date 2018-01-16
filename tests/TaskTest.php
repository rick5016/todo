<?php

use PHPUnit\Framework\TestCase;

define('ROOT_PATH', realpath(dirname(__FILE__) . '/../'));

function autoloader($className)
{
    if (file_exists(ROOT_PATH . '/library/mvc/' . $className . '.php')) {
        require_once ROOT_PATH . '/library/mvc/' . $className . '.php';
    }
}

class TaskTest extends TestCase
{
    // vendor/bin/phpunit --bootstrap vendor/autoload.php tests/TaskTest
    
    public function __construct($name = null, array $data = array(), $dataName = '')
    {
        require_once ROOT_PATH . '/modeles/Autoloader.php';

        spl_autoload_register('autoloader');
        Model::init();
        parent::__construct($name, $data, $dataName);
    }
    
    /**
     * @dataProvider additionTask
     */
    public function testSave($task)
    {
        $return = $task->save();
        $this->assertInstanceOf('ORM_Task', $return);
    }
    public function additionTask()
    {
        $dateStart = date('Y-m-d');
        $dateEnd = date('Y-m-d');
        $projectIds = array(1, 2, 3);
        $return = array();
        foreach ($projectIds as $projectId)
        {
            for ($i = 1; $i<3; $i++) {
                $return[] = array(new ORM_Task(array('idProject' => $projectId, 'name' => 'Task ' . $i, 'dateStart' => $dateStart, 'dateEnd' => $dateEnd)));
            }
        }
        return $return;
    }
    
    public function testSelect()
    {
        $tasks = Model::factory('task')->load();
        $return = false;
        foreach ($tasks as $task) {
            $return = ($task instanceof ORM_Task) ? true : false;
        }
        $this->assertTrue($return);
    }
    
    public function testUpdate()
    {
        // Projet 1 = Maison
        // Projet 2 = Travail
        // Projet 3 = Sport
        $return = false;
        $tasks = Model::factory('task')->load();
        
        $tasks[0]->setName('Ménage salon');
        $tasks[0]->setReiterate(3); // Tous les x mois
        $tasks[0]->setInterspace(3); // Tous les 3 mois
        
        $tasks[1]->setName('Ménage salle de bain');
        $tasks[1]->setReiterate(2); // Toutes les x semaines
        $tasks[1]->setInterspace(2); // Toutes les 2 semaines
        
        $tasks[2]->setName('Dossier Client'); // unique
        
        $tasks[3]->setName('Trie Email');
        $tasks[3]->setReiterate(1); // Tous les x jours
        $tasks[3]->setInterspace(2); // Tous les 2 jours
        
        $tasks[4]->setName('Course à pied');
        $tasks[4]->setReiterate(2); // Toutes les x semaines
        $tasks[4]->setInterspace(1); // Toutes les 1 semaines
        
        $tasks[5]->setName('Althères');
        $tasks[5]->setReiterate(1); // Tous les jours
        $tasks[5]->setInterspace(1); // Tous les 1 jour
        
        foreach ($tasks as $task) {
            $t = $task->save();
            $return = ($t instanceof ORM_Task) ? true : false;
        }
        $this->assertTrue($return);
    }
    
    public function testDelete()
    {
        $project = new ORM_Project(array('name' => 'delete'));
        $project->save();
        $task = new ORM_Task(array('idProject' => $project->getId(), 'name' => 'delete', 'dateStart' => date('Y-m-d'), 'dateEnd' => date('Y-m-d')));
        $task->save();
        $return = $task->delete();
        $this->assertTrue($return);
        
        $taskBDD = Model::factory('task')->setWhere(array('name' => 'delete'))->loadOne();
        $this->assertFalse($taskBDD);
    }
    
    public function testDeleteCascad()
    {
        $project = Model::factory('project')->setWhere(array('name' => 'delete'))->loadOne();
        $task = new ORM_Task(array('idProject' => $project->getId(), 'name' => 'delete', 'dateStart' => date('Y-m-d'), 'dateEnd' => date('Y-m-d')));
        $task->save();
        
        $return = $project->delete(true);
        $this->assertTrue($return);
        
        $projectBDD = Model::factory('project')->setWhere(array('name' => 'delete'))->loadOne();
        $this->assertFalse($projectBDD);
        
        $taskBDD = Model::factory('task')->setWhere(array('name' => 'delete'))->loadOne();
        $this->assertFalse($taskBDD);
    }
    
    public function testPerforme()
    {
        $tasks = Model::factory('task')->load();
        foreach ($tasks as $task) {
            $task->performe();
        }
        $tasksORM = Model::factory('task')->getPerformed();
        
        foreach ($tasksORM as $taskOrm)
        {
            $dateTimeStart = new DateTime(date('Y-m-d'));
            $dateTimeEnd = new DateTime(date('Y-m-d'));
            
            // Calcul de l'interval pour la nouvelle date de fin
            $diff = $dateTimeStart->diff($dateTimeEnd);
            $interval = $diff->format('%a');
            
            $dateTime = $dateTimeStart;
            $interspace = $taskOrm->getInterspace();
            $reiterate_type = 'D';
            if ($taskOrm->getReiterate() == 2) { // Semaine
                $interspace *= 7;
            } elseif ($taskOrm->getReiterate() == 3) {
                $reiterate_type = 'M';
            } elseif ($taskOrm->getReiterate() == 4) {
                $reiterate_type = 'Y';
            }

            $dateTime->add(new DateInterval('P' . $interspace . $reiterate_type));
            
            $dateStartNew = $dateTime->format('Y-m-d') . ' 00:00:00';
            $dateTimeStartNew = new DateTime($dateStartNew);
            

            // Calcul de la date de fin
            $dateTimeStartNew->add(new DateInterval('P' . $interval . 'D'));
            $dateEndNew = $dateTimeStartNew->format('Y-m-d') . ' 00:00:00';
            
            $this->assertEquals($dateStartNew, $taskOrm->getDateStart(), 'name: ' . $taskOrm->getName() . ' reiterate: ' . $taskOrm->getReiterate() . ' interspace: ' . $taskOrm->getInterspace());
            $this->assertEquals($dateEndNew, $taskOrm->getDateEnd());
            
        }
    }
}
