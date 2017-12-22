<?php

class Front_InboxController extends Controller
{
    
    function taskAction()
    {
        $task_id = $this->getRequest()->getParam('id');
        if (isset($task_id) && !empty($task_id))
        {
            $task                     = Model::factory('task')->loadOne(true, array('task.id' => $task_id));
            $this->view->modification = true;
        }
        else
        {
            $task            = new ORM_Task();
            $this->view->new = true;
        }
        
        // Création du nouveau projet pour l'intégrer dans le formulaire de modification
        if ($this->getRequest()->isPost())
        {
            $params     = $this->getRequest()->getParams();
            $project_id = $params['project'];
            if (empty($project_id) && !empty($params['project_new']))
            {
                $project           = new ORM_Project(array('name' => $params['project_new']));
                $project->save();
                $params['project'] = $project->getId();
            }
        }

        $form = new Form_task($task, Model::factory('project')->load(false));
        if ($this->getRequest()->isPost())
        {

            if ($form->isValid($params))
            {
                $task       = ORM_Repository::factory('task')->saveTask($params, $task);
                $this->view->modification = true;
                $this->view->validation   = true;
            }
        }
        $this->view->task     = $task;
        $this->view->form     = $form;
    }
    
    function indexAction()
    {
        $tasks    = array();
        $datas    = array();
        $form     = new Form_filtres();
        $form->isValid($this->getRequest());

        foreach (ORM_Repository::factory('task')->loadInbox($this->getRequest()) as $task)
        {
            $task->setDateAffichage();
            $task->setMoment(); // A revoir
            $task->setNbPerforme();
            
            // TODO : a revoir 
            // 1 : en fonction de l'heure de la journée
            // 2 : les tâches ne se finissant pas aujourd'hui doivent etre en fin de liste
            $date_affichage_dateTime = new DateTime($task->dateAffichage);
            $datas[$date_affichage_dateTime->format('Y-m-d-H-i') . '-' . $task->priority][] = $task;
        }
        ksort($datas);
        foreach ($datas as $data)
        {
            foreach ($data as $taskObj) {
                $tasks[] = $taskObj;
            }
        }
        
        $this->view->filtre   = $this->getRequest()->getParam('filtre');
        $this->view->details  = (isset($_SESSION['details']) && $_SESSION['details']);
        $this->view->priority = $this->getRequest()->getParam('priority', '11111');;
        $this->view->form     = $form;
        $this->view->tasks    = $tasks;
    }
    
    function deleteAction()
    {
        $id = $this->getRequest()->getParam('id');
        
        if (isset($id)) {
            Model::factory('task')->delete(true, $id);
        }
        
        header('Location: ' . $this->getRequest()->getReferer());
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
            ORM_Repository::factory('task')->deleteLastPerforme($id, $idPerforme);
        }
        
        header('Location: ' . $this->getRequest()->getReferer());
        exit;
    }
}

