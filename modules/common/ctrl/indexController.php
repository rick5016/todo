<?php

class IndexController extends Controller
{
    
    function iaAction()
    {
        $phrase = $this->getRequest()->getParam('phrase');

        // apprendre
        if (isset($phrase))
        {
            $mots = explode(' ', $phrase);
            
            foreach ($mots as $mot)
            {
                $mot  = trim(strtolower($mot));
                $mot  = str_replace('"', "'", $mot);
                $mot  = str_replace('.', " ", $mot);
                $mot  = str_replace(',', " ", $mot);
                $mot  = str_replace(';', " ", $mot);
                if (!empty($mot))
                {
                    $find = Model::factory('ia')->load(false, array('mot' => '"' . $mot . '"'));

                    if (!$find)
                    {
                        $ia = new ORM_Ia(array('mot' => $mot));
                        $ia->save();
                    }
                }
            }
        }

        // comprendre
        $arrays = array('ajouter', 'supprimer', 'projet', 'tache', 'quand', 'titre', 'reiterate', 'quandJour', 'quandMois');
        
        // Ajouter/créer (supprimer/retirer) tâche (projet) aujourd'hui/maintenant titre/s'appele nomdelatâche tous les (x) jours (ou unique)
        $ajouter   = array('ajou', 'cre');
        $supprimer = array('sup', 'retir');
        $projet    = array('projet');
        $tache     = array('tach');
        $titre     = array('titre', 'apel', 'appel', 'proj', 'tach', 'nomm');
        $titresCrit = array('titre', 'apel', 'appel', 'proj', 'tach', 'nomm');
        $titrepos  = false;
        $quand     = array('aujourd', 'maintenan');
        $reiterate = array('tou');
        $quandJour = array('jour');
        $quandMois = array('moi');

        foreach ($arrays as $array) {
            $val = false;
            foreach ($$array as $haystack) {
                if (strpos($phrase, $haystack) !== false) {
                    $val = true;
                }
            }
            $$array = $val;
        }
        
        // Répondre
        if (!$ajouter && !$supprimer)
            $retour = 'Quelle action souhaitez-vous effectuer ? Supprimer ou ajouter ?';
        else
        {
            $retour = ($ajouter) ? 'Ajouter' : '';
            $retour = (!$ajouter && $supprimer) ? 'Supprimer' : $retour;
            
            if (!$projet && !$tache)
                $retour = 'Souhaitez vous ' . $retour . ' une tâche ou un projet ?';
            else
            {
                $retour .= ($projet) ? ' un projet' : '';
                $retour .= (!$projet && $tache) ? ' une tâche' : '';
                
                if ($titre)
                {
                    foreach ($mots as $key => $data)
                    {
                        foreach ($titresCrit as $titreCrit)
                        {
                            if (strpos($data, $titreCrit) !== false) {
                                $titrepos = $key+1;
                            }
                        }
                    }
                    
                    if (isset($mots[$titrepos]))
                    {
                        $retour .= ($titre) ? ' ayant pour titre : ' . $mots[$titrepos] : '';

                        echo json_encode($retour . ' ?');
                        exit;
                    }
                    
                }
                
                $retour = 'Vous souhaitez ' . $retour . '. Il manque le titre';
                
            }
        }

        echo json_encode($retour);
        exit;
    }
    
    function loginAction()
    {
        if (isset($_SESSION['user']))
        {
            header('Location: http://' . $_SERVER['SERVER_NAME'] . ROOT_ACCUEIL);
            exit();
        }
        $loginSubmit = $this->getRequest()->getParam('login-submit');
        $registerSubmit = $this->getRequest()->getParam('register-submit');
        
        $this->template = false;
        $formLogin = new Form_login();
        $formRegister = new Form_register();
        $params = $this->getRequest()->getParams();
        if ($this->getRequest()->isPost() && isset($loginSubmit) && $formLogin->isValid($params)) {
            header('Location: http://' . $_SERVER['SERVER_NAME'] . ROOT_ACCUEIL);
        }
        if ($this->getRequest()->isPost() && isset($registerSubmit))
        {
            $this->view->registerDisplay = true;
            if ($formRegister->isValid($params))
            {
    //            $user = $this->loadOne(false, array('login' => $params['login'], 'password' => $params['password']));
    //            if ($user) {
    //                $_SESSION['user'] = $user;
    //            }
            }
        }
        $this->view->login = $formLogin;
        $this->view->register = $formRegister;
    }
    
    function logoutAction()
    {
        Model::factory('user')->logout();
        header('Location: http://' . $_SERVER['SERVER_NAME'] . '/login');
    }

    function indexAction()
    {
        if (!isset($_SESSION['user']))
        {
            header('Location: http://' . $_SERVER['SERVER_NAME'] . '/login');
            exit();
        }
        
        $projects = Model::factory('project')->load(false, array('active' => 1));
        if (!isset($_SESSION['project']))
        {
            $_SESSION['project'] = array();
            foreach ($projects as $project) {
                $_SESSION['project'][] = $project->getId();
            }
        }
        $this->view->user                 = (isset($_SESSION['user'])) ? $_SESSION['user'] : false;
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
