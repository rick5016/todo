<?php

class Form_task extends form
{
    function __construct($projects)
    {
        $this->add($name = new Element_Text('task_name'));
        $name->setRequired(true);
        $name->setLibelle('Titre');
        $name->setStyle(array('margin-left' => '25px'));
        $name->setGlyphicon('glyphicon-tasks');
        
        $this->add($projectsElement = new Element_Select('project_id'));
        $projectsElement->setLibelle('Projet');
        $projectsElement->setOptions($projects);
        $projectsElement->setStyle(array('margin-left' => '25px'));
        $projectsElement->setGlyphicon('glyphicon-calendar');
        
        $this->add($priority = new Element_Radio('priority'));
        $priority->setValues(array('0', '1', '2', '3'));
        $priority->setValue(0);
        $priority->setGlyphicon('glyphicon-chevron-up');
        $priority->setStyle(array('margin-left' => '25px', 'padding' => '0'));
        $priority->setStyles(array(
            array('color' => '#c0c0c0', 'font-weight' => 'bold'),
            array('color' => '#d9534f', 'font-weight' => 'bold'),
            array('color' => '#f0ad4e', 'font-weight' => 'bold'),
            array('color' => '#e8dc00', 'font-weight' => 'bold')
        ));
        
        $this->add(new Element_Checkbox('allDay'));
        
        $this->add($dateStart = new Element_Text('dateStart'));
        $dateStart->setValue(date('d/m/Y'));
        
        $this->add($timeStart = new Element_Text('timeStart'));
        $timeStart->setAttributs(array('size' => '2'));
        
        $this->add($dateEnd = new Element_Text('dateEnd'));
        $dateEnd->setValue(date('d/m/Y'));
        
        $this->add($timeEnd = new Element_Text('timeEnd'));
        $timeEnd->setAttributs(array('size' => '2'));
        
        $this->add($repeat = new Element_Select('repeat'));
        $repeat->setOptions(array('Once', 'Days', 'Weeks', 'Months', 'Years'));
        $repeat->setGlyphicon('glyphicon-repeat');
        
        $this->add($reiterateEnd = new Element_Select('reiterateEnd'));
        $reiterateEnd->setOptions(array('Toujours', "Jusqu'à une certains date", "Jusqu'à un nombre de fois"));
        
        $this->add(new Element_Text('untilDate'));
        
        $this->add($untilNumber = new Element_Text('untilNumber'));
        $untilNumber->setAttributs(array('size' => '2'));
        $untilNumber->setValue('0');
        
        $this->add($interspace = new Element_Text('interspace'));
        $interspace->setAttributs(array('size' => '1'));
        $interspace->setValue('1');
        
        $this->add($submit = new Element_Submit('submit'));
        $submit->setValue('Valider');
    }
    
}
