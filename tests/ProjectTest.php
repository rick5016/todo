<?php

use PHPUnit\Framework\TestCase;

define('ROOT_PATH', realpath(dirname(__FILE__) . '/../'));

function autoloader($className)
{
    if (file_exists(ROOT_PATH . '/library/mvc/' . $className . '.php')) {
        require_once ROOT_PATH . '/library/mvc/' . $className . '.php';
    }
}

class ProjectTest extends TestCase
{
    // vendor/bin/phpunit --bootstrap vendor/autoload.php tests/ProjectTest
    
    public function __construct($name = null, array $data = array(), $dataName = '')
    {
        require_once ROOT_PATH . '/modeles/Autoloader.php';

        spl_autoload_register('autoloader');
        Model::init();
        parent::__construct($name, $data, $dataName);
    }
    
    /**
     * @dataProvider additionTable
     */
    public function testClean($table)
    {
        $return = Model::factory($table)->truncate();
        $this->assertTrue($return);
    }
    public function additionTable()
    {
        return array(
            array('performe'),
            array('task'),
            array('project')
        );
    }
    
    /**
     * @dataProvider additionProject
     */
    public function testSave($project)
    {
        $return = $project->save();
        $this->assertInstanceOf('ORM_Project', $return);
    }
    public function additionProject()
    {
        return array(
            array(new ORM_Project(array('name' => 'Projet 1'))),
            array(new ORM_Project(array('name' => 'Projet 2'))),
            array(new ORM_Project(array('name' => 'Projet 3'))),
            array(new ORM_Project(array('name' => 'Projet 4')))
        );
    }
    
    public function testSelect()
    {
        $projects = Model::factory('project')->load();
        $return = false;
        foreach ($projects as $project) {
            $return = ($project instanceof ORM_Project) ? true : false;
        }
        $this->assertTrue($return);
    }
    
    public function testUpdate()
    {
        $return = false;
        $projects = Model::factory('project')->load();
        $projects[0]->setName('Maison');
        $projects[1]->setName('Travail');
        $projects[2]->setName('Sport');
        $projects[3]->setName('delete');
        foreach ($projects as $project)
        {
            $p = $project->save();
            $return = ($p instanceof ORM_Project) ? true : false;
        }
        
        $this->assertTrue($return);
    }
    
    public function testDelete()
    {
        $project = Model::factory('project')->setWhere(array('name' => 'delete'))->loadOne();
        $return = $project->delete();
        $this->assertTrue($return);
    }
}
