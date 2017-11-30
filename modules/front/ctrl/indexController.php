<?php

class Front_IndexController extends Controller
{
    
    function indexAction()
    {
        echo $this->view->render(array());
    }
    
    function taskAction()
    {
        $task_id = $this->getRequest()->getParam('id');
        if (isset($task_id) && !empty($task_id)) {
            $task = Model::factory('task')->loadOne(true, array('task.id' => $task_id));
        }
        
        $form = new Form_task(Model::factory('project')->load(false));
        if ($this->getRequest()->isPost())
        {
            if ($form->isValid($this->getRequest()->getParams())) {
                if (!isset($task))
                {
                    $task = new ORM_Task();
                    $new  = true;
                }
                $task                = ORM_Repository::factory('task')->saveTask($this->getRequest()->getParams(), $task);
                $this->view->task_id = $task->id;
                $this->view->valide = isset($new) ? 'Tache enregistrée (id = ' . $task->id . ')' : 'Tache modifiée (id = ' . $task->id . ')';
            }
        }
        $this->view->form         = $form;
        $this->view->projects     = Model::factory('project')->load(false);
    }
    
    function inboxAction()
    {
        $tri = array();
        $clause = array();
        $filtres = new Form_filtres();
        if (isset($_GET['filtre']))
        {
            if ($_GET['filtre'] == "today")
            {
                $vars['filtre_today'] = true;
                $vars = $filtres->check(array('filtrer' => true), $vars, false);
            }
            elseif ($_GET['filtre'] == "project")
            {
                $vars = $filtres->check($_POST, $vars);
                $clause[] = "project.id = " . (int) $_GET['id'];
            }
        } else {
            $vars = $filtres->check($_POST, $vars);
        }

        $vars['priority'] = '11111';
        if (isset($_GET['priority'])) {
            $vars['priority'] = $_GET['priority'];
        }
        $tasks = ORM_Repository::factory('task')->loadInbox($clause, $vars['priority']);
        foreach ($tasks as $task)
        {
            $afficher_la_tache = true;

            // Afficher ou non les dates après Aujourd'hui
            if (!isset($vars['ant']) && $task->dateStart > date('Y-m-d H:i')) {
                $afficher_la_tache = false;
            }

            if ($afficher_la_tache)
            {
                $date_affichage = $task->dateStart;

                // Si la date d'aujoud'hui ce situe entre le début et la fin de l'événement alors la date d'affiche est la date d'aujourd'hui
                // Si la date de fin est passé et que retierate <> de 0 alors la date d'affiche est la date d'aujourd'hui
                if (($date_affichage < date('Y-m-d H:i') && $task->dateEnd >  date('Y-m-d H:i')) || ($task->dateEnd <  date('Y-m-d H:i') && $task->reiterate != 0)) {
                    $date_affichage = date('Y-m-d H:i') . ':00';
                }
                $task->dateAffichage = $date_affichage;

                // Afficher ou non les dates avant aujourd'hui
                if (!isset($vars['past']) && ($task->dateAffichage < date('Y-m-d H:i'))) {
                    $afficher_la_tache = false;
                }

                if ($afficher_la_tache)
                {
                    $date_affichage_dateTime    = new DateTime($date_affichage);
                    $dateStart_dateTime         = new DateTime($task->dateStart);
                    $dateEnd_dateTime           = new DateTime($task->dateEnd);
                    $timeStart                  = $dateStart_dateTime->format('H:i');
                    $timeEnd                    = $dateEnd_dateTime->format('H:i');
                    $moment = 3; // Toute la journée
                    if ($timeStart != "00:00" || $timeEnd != "00:00")
                    {
                        if ($timeStart == "00:00" and $timeEnd == "11:59") {
                            $moment = 1; // Matin
                        } elseif ($timeStart == "12:00" && ($timeEnd == "17:59" || $timeEnd == "23:59")) {
                            $moment = 2; // Après-midi et soir
                        } elseif ($timeStart == "18:00" and $timeEnd == "23:59") {
                            $moment = 4; // soir
                        }
                    }

                    // TODO : ordre a revoir 
                    // 1 : en fonction de l'heure de la journée
                    // 2 : les tâches ne se finissant pas aujourd'hui doivent etre en fin de liste
                    $tri[$date_affichage_dateTime->format('Y-m-d') . '-' . $moment . '-' . $task->priority . '-' . $task->id][] = $task;
                }
                $task->nbPerforme = $task->count();
            }
        }
        ksort($tri);
        $return = array();
        foreach ($tri as $datas)
        {
            foreach ($datas as $data) {
                $return[] = $data;
            }
        }

        $vars['tasks'] = $return;

//        return $vars;
    }
    
    function calendrierAction()
    {
        // Récuperation des variables passées, on donne soit année; mois; année+mois
        if(!isset($_GET['mois'])) $num_mois = date("n"); else $num_mois = $_GET['mois'];
        if(!isset($_GET['annee'])) $num_an = date("Y"); else $num_an = $_GET['annee'];

        // pour pas s'embeter a les calculer a l'affchage des fleches de navigation...
        if($num_mois < 1) { $num_mois = 12; $num_an = $num_an - 1; }
        elseif($num_mois > 12) {	$num_mois = 1; $num_an = $num_an + 1; }

        // nombre de jours dans le mois et numero du premier jour du mois
        $int_nbj = date("t", mktime(0,0,0,$num_mois,1,$num_an));
        $int_premj = date("w",mktime(0,0,0,$num_mois,1,$num_an));

        // tableau des jours, tableau des mois...
        $tab_jours = array("","Lu","Ma","Me","Je","Ve","Sa","Di");
        $tab_mois = array("","Janvier","Fevrier","Mars","Avril","Mai","Juin","Juillet","Aout","Septembre","Octobre","Novembre","Decembre");

        $int_nbjAV = date("t", mktime(0,0,0,($num_mois-1<1)?12:$num_mois-1,1,$num_an)); // nb de jours du moi d'avant
        $int_nbjAP = date("t", mktime(0,0,0,($num_mois+1>12)?1:$num_mois+1,1,$num_an)); // nb de jours du mois d'apres

        // Récupérer tout les taches du moi $num_mois, $num_mois-1 et $num_mois+1 de $num_an
        $tasks = Model::factory('task')->load();

        // on affiche les jours du mois et aussi les jours du mois avant/apres, on les indique par une * a l'affichage on modifie l'apparence des chiffres *
        $tab_cal = array(array(),array(),array(),array(),array(),array()); // tab_cal[Semaine][Jour de la semaine]
        $int_premj = ($int_premj == 0)?7:$int_premj;
        $t = 1; $p = "";

        $vars['num_an']     = $num_an;
        $vars['num_mois']   = $num_mois;
        $vars['tab_mois']   = $tab_mois;
        $vars['tab_jours']  = $tab_jours;

        for($i=0;$i<6;$i++)
        {
            for($j=0;$j<7;$j++)
            {
                if($j+1 == $int_premj && $t == 1) // on stocke le premier jour du mois
                {
                    $tab_cal[$i][$j] = $this->_calDay($p.$t, $num_an, $num_mois, $t, $tasks);
                    $t++;
                }
                elseif($t > 1 && $t <= $int_nbj) // on incremente a chaque fois...
                {
                    $tab_cal[$i][$j] = $this->_calDay($p.$t, $num_an, $num_mois, $t, $tasks);
                    $t++;
                }
                elseif($t > $int_nbj) // on a mis tout les numeros de ce mois, on commence a mettre ceux du suivant
                {
                    $t = 1;
                    $num_mois++;
                    $p="*";
                    $tab_cal[$i][$j] = $this->_calDay($p.$t, $num_an, $num_mois, $t, $tasks);
                    $t++;
                }
                elseif($t == 1) // on a pas encore mis les num du mois, on met ceux de celui d'avant
                {
                    $tab_cal[$i][$j] = $this->_calDay("*".($int_nbjAV-($int_premj-($j+1))+1), $num_an, $num_mois, $t, $tasks);
                }
            }
        }
        $vars['tab_cal']    = $tab_cal;

        return $vars;

    }
    
    
    function _calDay($day, $num_an, $num_mois, $t, $tasks)
    {
        $data = array($day);
        $date = $num_an . '-' . str_pad($num_mois, 2, 0, STR_PAD_LEFT) . '-' . str_pad($t, 2, 0, STR_PAD_LEFT);
        $date_compare = $date . ' 00:00:00';
        foreach ($tasks as $task)
        {
            $reiterate                  = $task->reiterate;
            $dateStartOrigine           = $task->dateStartOrigine;
            $dateStartOrigine_datetime    = new DateTime($task->dateStartOrigine);
            $dateEndOrigine_datetime    = new DateTime($task->dateEndOrigine);
            $dateEndOrigine             = $dateEndOrigine_datetime->format('Y-m-d') . ' 23:59:59';
            if ($reiterate > 0 && !($dateStartOrigine <= $date_compare && $date_compare <= $dateEndOrigine))
            {
                // Calcul de l'interval
                $diff = $dateStartOrigine_datetime->diff($dateEndOrigine_datetime);
                $interval = $diff->format('%a');

                $dateStartOrigine = $task->getNewDate($dateStartOrigine, $reiterate, $task->interspace, 'last', $date);
                $dateStartOrigine_datetime = new DateTime($dateStartOrigine);
                $dateStartOrigine_datetime->add(new DateInterval('P' . $interval . 'D'));
                $dateEndOrigine = $dateStartOrigine_datetime->format('Y-m-d') . ' 23:59:59';
                $task->setDateStartOrigine($dateStartOrigine);
                $task->setDateEndOrigine($dateEndOrigine);
            }
            if ($dateStartOrigine <= $date_compare && $date_compare <= $dateEndOrigine)
            {
                $performe = 0;
                $performeSearch = Model::factory('performe')->loadOne(false, array('idTask' => $task->id, "created >= '$dateStartOrigine' and created <= '$dateEndOrigine'"));
                if ($performeSearch) {
                    $performe = 1;
                }
                elseif ($date > date('Y-m-d')) {
                    $performe = 2;
                }
                $data[] = array($task, $performe);
            }
        }
        return $data;
    }
    function delAction()
    {
        Model::factory('task')->delete(true, $_GET['id']); 
    }

    function doneAction()
    {
        $task = Model::factory('task')->loadOne(false, array('id' => $_GET['id']));
        if (isset($task)) {
            $task->performe();
        } 
    }

    function cancelAction()
    {
        Model::factory('task')->deleteLastPerforme($_GET['id'], $_GET['idPerforme']); 
    }
}

