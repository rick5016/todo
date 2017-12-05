<?php

class Form_task extends form
{
    function __construct($task, $projectsObjs)
    {
        $this->add($name = new Element_Text('task_name'));
        $name->setRequired(true);
        $name->setLibelle('Titre');
        $name->setStyle(array('margin-left' => '25px'));
        $name->setGlyphicon('glyphicon-tasks');
        $name->setValue($task->getName());
        
        $this->add($project = new Element_Select('project_id'));
        $project->setLibelle('Projet');
        
        $projects = array();
        foreach ($projectsObjs as $projectObj)
        {
            $projects[$projectObj->getId()] = $projectObj->getName();
        }
        
        $project->setOptions($projects);
        $project->setStyle(array('margin-left' => '25px'));
        $project->setGlyphicon('glyphicon-calendar');
        $project->setValue($task->getIdProject());
        
        $this->add($priority = new Element_Radio('priority'));
        $priority->setValues(array('0', '1', '2', '3'));
        $priority->setValue(isset($task->id) ? $task->getPriority() : 0);
        $priority->setGlyphicon('glyphicon-chevron-up');
        $priority->setStyle(array('margin-left' => '25px', 'padding' => '0'));
        $priority->setStyles(array(
            array('color' => '#c0c0c0', 'font-weight' => 'bold'),
            array('color' => '#d9534f', 'font-weight' => 'bold'),
            array('color' => '#f0ad4e', 'font-weight' => 'bold'),
            array('color' => '#e8dc00', 'font-weight' => 'bold')
        ));
        
        $this->add($allDay = new Element_Checkbox('allDay'));
//        $allDay->setValue($task->getAllDay());
        
        $dateStartValue = date('d/m/Y');
        $dateEndValue   = date('d/m/Y');
        if (isset($task->id))
        {
            $dateStart_dateTime = new DateTime($task->getDateStart());
            $dateEnd_dateTime   = new DateTime($task->getDateFin());
            $dateStartValue     = $dateStart_dateTime->format('d/m/Y');
            $dateEndValue       = $dateEnd_dateTime->format('d/m/Y');
            $timeStartValue     = $dateStart_dateTime->format('H:i');
            $timeEndValue       = $dateEnd_dateTime->format('H:i');
        }

        $this->add($dateStart = new Element_Text('dateStart'));
        $dateStart->setValue($dateStartValue);
        
        $this->add($timeStart = new Element_Text('timeStart'));
        $timeStart->setAttributs(array('size' => '2'));
        $timeStart->setValue($timeStartValue);
        
        $this->add($dateEnd = new Element_Text('dateEnd'));
        $dateEnd->setValue($dateEndValue);
        
        $this->add($timeEnd = new Element_Text('timeEnd'));
        $timeEnd->setAttributs(array('size' => '2'));
        $timeEnd->setValue($timeEndValue);
        
        $this->add($repeat = new Element_Select('repeat'));
        $repeat->setOptions(array('Once', 'Days', 'Weeks', 'Months', 'Years'));
        $repeat->setGlyphicon('glyphicon-repeat');
        $repeat->setValue($task->getReiterate());
        
        $this->add($reiterateEnd = new Element_Select('reiterateEnd'));
        $reiterateEnd->setOptions(array('Toujours', "Jusqu'à une certains date", "Jusqu'à un nombre de fois"));
        $reiterateEnd->setValue($task->getReiterateEnd());
        
        $this->add($untilDate = new Element_Text('untilDate'));
        $untilDate->setValue($task->getUntilDate());
        
        $this->add($untilNumber = new Element_Text('untilNumber'));
        $untilNumber->setAttributs(array('size' => '2'));
        $untilNumber->setValue(isset($task->id) ? $task->getUntilNumber() : '0');
        
        $this->add($interspace = new Element_Text('interspace'));
        $interspace->setAttributs(array('size' => '1'));
        $interspace->setValue(isset($task->id) ? $task->getInterspace() : '1');
        
        $this->add($submit = new Element_Submit('submit'));
        $submit->setValue('Valider');
    }
    
}
