<?php

class IndexController extends Controller
{

    function indexAction()
    {
        $projects = Model::factory('project')->load(false, array('active' => 1));
        if (!isset($_SESSION['project']))
        {
            $_SESSION['project'] = array();
            foreach ($projects as $project) {
                $_SESSION['project'][] = $project->getId();
            }
        }
        $this->view->projects             = $projects;
        $this->view->projectselections    = isset($_SESSION['project']) ? $_SESSION['project'] : array();
        $this->view->projectselectionsdel = isset($_SESSION['projectdel']) ? $_SESSION['projectdel'] : array();
    }

    function projectsAction()
    {
        $filtre = $this->getRequest()->getParam('filtre');
        $idProject = $this->getRequest()->getParam('id');
        
        if ((isset($filtre) && isset($idProject)))
        {
            if ($filtre == 'project')
            {
                if (isset($_SESSION['project']))
                {
                    if (in_array((int) $idProject, $_SESSION['projectdel'])) {
                        unset($_SESSION['projectdel'][array_search((int) $idProject, $_SESSION['projectdel'])]);
                    }
                    if (!in_array($idProject, $_SESSION['project'])) {
                        $_SESSION['project'][] = (int) $idProject;
                    }
                }
                else {
                    $_SESSION['project'] = array((int) $idProject);
                }
            }
            elseif ($filtre == 'projectdel')
            {
                if (isset($_SESSION['project']) && in_array((int) $idProject, $_SESSION['project'])) {
                    unset($_SESSION['project'][array_search((int) $idProject, $_SESSION['project'])]);
                }
                if (isset($_SESSION['projectdel']) && !in_array($idProject, $_SESSION['projectdel'])) {
                    $_SESSION['projectdel'][] = (int) $idProject;
                } else {
                    $_SESSION['projectdel'] = array((int) $idProject);
                }
            }
        }
        echo json_encode('');
        exit;
    }
    
    function projectscolorAction()
    {
        $idProject = $this->getRequest()->getParam('id');
        $color     = $this->getRequest()->getParam('color');

        $project = Model::factory('project')->loadOne(false, array('id' => (int) $idProject));
        if ($project) {
            $project->setColor($color);
            $project->save();
        }
        echo json_encode('');
        exit;
    }
    
    function projectsactivationAction()
    {
        $idProject = $this->getRequest()->getParam('id');

        $project = Model::factory('project')->loadOne(false, array('id' => (int) $idProject));
        if ($project) {
            $project->setActive(0);
            $project->save();
        }
        echo json_encode('');
        exit;
    }

}
