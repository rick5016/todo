<?php

class Front_InboxController extends Controller
{
    
    function taskAction()
    {
        $task_id = $this->getRequest()->getParam('id');
        if (isset($task_id) && !empty($task_id)) {
            $task = Model::factory('task')->loadOne(true, array('task.id' => $task_id));
        } else
        {
            $task = new ORM_Task();
            $this->view->new  = true;
        }
        
        $form = new Form_task($task, Model::factory('project')->load(false));
        if ($this->getRequest()->isPost())
        {
            if ($form->isValid($this->getRequest()->getParams()))
            {
                $task             = ORM_Repository::factory('task')->saveTask($this->getRequest()->getParams(), $task);
                $this->view->task = $task;
            }
        }
        $this->view->form         = $form;
        $this->view->projects     = Model::factory('project')->load(false);
    }
    
    function indexAction()
    {
        $return   = array();
        $tri      = array();
        $clause   = array();
        $priority = $this->getRequest()->getParam('priority', '11111');
        $filtre   = $this->getRequest()->getParam('filtre');
        $form     = new Form_filtres();
        $form->isValid($this->getRequest());

        $tasks = ORM_Repository::factory('task')->loadInbox($clause, $priority, $filtre);
        foreach ($tasks as $task)
        {
            $task->setDateAffichage();
            $task->setMoment();

            // TODO : ordre a revoir 
            // 1 : en fonction de l'heure de la journée
            // 2 : les tâches ne se finissant pas aujourd'hui doivent etre en fin de liste
            $date_affichage_dateTime = new DateTime($task->dateAffichage);
            $tri[$date_affichage_dateTime->format('Y-m-d-H-i') . '-' . $task->priority][] = $task;

            $task->nbPerforme = $task->count();
        }
        ksort($tri);
        foreach ($tri as $datas)
        {
            foreach ($datas as $taskObj) {
                $return[] = $taskObj;
            }
        }
        
        $this->view->filtre   = $filtre;
        $this->view->details  = (isset($_SESSION['details']) && $_SESSION['details']);
        $this->view->priority = $priority;
        $this->view->form     = $form;
        $this->view->tasks    = $return;
    }
    
    function deleteAction()
    {
        $id = $this->getRequest()->getParam('id');
        
        if (isset($id)) {
            Model::factory('task')->delete(true, $id);
        }
        
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit;
    }

    function performeAction()
    {
        $_SESSION['action']['performe'] = ORM_Repository::factory('task')->performer($this->getRequest());
        header('Location: ' . $this->getRequest()->getReferer());
        exit;
    }

    function cancelAction()
    {
        $id = $this->getRequest()->getParam('id');
        $idPerforme = $this->getRequest()->getParam('idPerforme');
        
        if (isset($id) && isset($idPerforme)) {
            Model::factory('task')->deleteLastPerforme($id, $idPerforme);
        }
        
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit;
    }
}

