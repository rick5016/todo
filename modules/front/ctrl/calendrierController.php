<?php

class Front_CalendrierController extends Controller
{

    function indexAction()
    {
        $tasks = Model::factory('task')->load();
        
        // Récuperation des variables passées, on donne soit année; mois; année+mois
        $num_mois   = $this->getRequest()->getParam('mois', date("n"));
        $num_an   = $this->getRequest()->getParam('annee', date("Y"));

        // pour pas s'embeter a les calculer a l'affchage des fleches de navigation...
        if ($num_mois < 1)
        {
            $num_mois = 12;
            $num_an   = $num_an - 1;
        }
        elseif ($num_mois > 12)
        {
            $num_mois = 1;
            $num_an   = $num_an + 1;
        }

        // nombre de jours dans le mois et numero du premier jour du mois
        $int_nbj   = date("t", mktime(0, 0, 0, $num_mois, 1, $num_an));
        $int_premjs = date("w", mktime(0, 0, 0, $num_mois, 1, $num_an));

        // tableau des jours, tableau des mois...
        $tab_jours = array("", "Lundi", "Mardi", "Mercredi", "Jeudi", "Vendredi", "Samedi", "Dimanche");
        $tab_mois  = array("", "Janvier", "Février", "Mars", "Avril", "Mai", "Juin", "Juillet", "Aout", "Septembre", "Octobre", "Novembre", "Décembre");

        // nb de jours du moi d'avant
        $int_nbjAV = date("t", mktime(0, 0, 0, ($num_mois - 1 < 1) ? 12 : $num_mois - 1, 1, $num_an));

        // on affiche les jours du mois et aussi les jours du mois avant/apres, on les indique par une * a l'affichage on modifie l'apparence des chiffres *
        $tab_cal   = array(array(), array(), array(), array(), array(), array()); // tab_cal[Semaine][Jour de la semaine]
        $int_premj = ($int_premjs == 0) ? 7 : $int_premjs;
        
        // Indicateurs
        $t = 1; // premier jour du mois
        $p = ""; // mois suivant

        $this->view->num_an    = $num_an;
        $this->view->num_mois  = $num_mois;
        $this->view->tab_mois  = $tab_mois;
        $this->view->tab_jours = $tab_jours;

        // Nombre de ligne (6 semaines)
        for ($i = 0; $i < 6; $i++)
        {
            // Nombre de colonne (7 jours)
            for ($j = 0; $j < 7; $j++)
            {
                if ($j + 1 == $int_premj && $t == 1) // on stocke le premier jour du mois
                {
                    $tab_cal[$i][$j] = $this->_calMonth($p . $t, $num_an, $num_mois, $t, $tasks);
                    $t++;
                }
                elseif ($t > 1 && $t <= $int_nbj) // on incremente a chaque fois...
                {
                    $tab_cal[$i][$j] = $this->_calMonth($p . $t, $num_an, $num_mois, $t, $tasks);
                    $t++;
                }
                elseif ($t > $int_nbj) // on a mis tout les numeros de ce mois, on commence a mettre ceux du suivant
                {
                    $t               = 1;
                    $num_mois++;
                    $p               = "*";
                    $tab_cal[$i][$j] = $this->_calMonth($p . $t, $num_an, $num_mois, $t, $tasks);
                    $t++;
                }
                elseif ($t == 1) // on a pas encore mis les num du mois, on met ceux de celui d'avant
                {
                    $tab_cal[$i][$j] = $this->_calMonth("*" . ($int_nbjAV - ($int_premj - ($j + 1)) + 1), $num_an, $num_mois, $t, $tasks);
                }
            }
        }

        $this->view->tab_cal = $tab_cal;
    }

    function _calMonth($day, $num_an, $num_mois, $t, $tasks)
    {
        if ($num_mois == 13)
        {
            $num_mois = 1;
            $num_an++;
        }
        
        $data         = array($day);
        $date         = $num_an . '-' . str_pad($num_mois, 2, 0, STR_PAD_LEFT) . '-' . str_pad($t, 2, 0, STR_PAD_LEFT);
        $date_compare = $date . ' 00:00:00';
        foreach ($tasks as $task)
        {
            $reiterate                 = $task->reiterate;
            $dateStartOrigine          = $task->dateStartOrigine;
            $dateStartOrigine_datetime = new DateTime($task->dateStartOrigine);
            $dateEndOrigine_datetime   = new DateTime($task->dateEndOrigine);
            $dateEndOrigine            = $dateEndOrigine_datetime->format('Y-m-d') . ' 23:59:59';
            if ($reiterate > 0 && !($dateStartOrigine <= $date_compare && $date_compare <= $dateEndOrigine))
            {
                // Calcul de l'interval
                $diff     = $dateStartOrigine_datetime->diff($dateEndOrigine_datetime);
                $interval = $diff->format('%a');

                $dateStartOrigine          = $task->getNewDate($dateStartOrigine, $reiterate, $task->interspace, 'last', $date);
                $dateStartOrigine_datetime = new DateTime($dateStartOrigine);
                $dateStartOrigine_datetime->add(new DateInterval('P' . $interval . 'D'));
                $dateEndOrigine            = $dateStartOrigine_datetime->format('Y-m-d') . ' 23:59:59';
                $task->setDateStartOrigine($dateStartOrigine);
                $task->setDateEndOrigine($dateEndOrigine);
            }
            if ($dateStartOrigine <= $date_compare && $date_compare <= $dateEndOrigine)
            {
                $performe       = 0;
                $performeSearch = Model::factory('performe')->loadOne(false, array('idTask' => $task->id, "created >= '$dateStartOrigine' and created <= '$dateEndOrigine'"));
                if ($performeSearch) {
                    $performe = 1;
                } elseif ($date > date('Y-m-d')) {
                    $performe = 2;
                }
                $data[] = array($task, $performe);
            }
        }
        return $data;
    }

    function _calDay($hour, $num_an, $num_mois, $day, $tasks)
    {
        if ($num_mois == 13)
        {
            $num_mois = 1;
            $num_an++;
        }
        
        $data         = array();
        $date         = $num_an . '-' . str_pad($num_mois, 2, 0, STR_PAD_LEFT) . '-' . str_pad($day, 2, 0, STR_PAD_LEFT);
        $date_compare = $date . ' ' . str_pad($hour, 2, 0, STR_PAD_LEFT);
        foreach ($tasks as $task)
        {
            $dateStart                 = $task->dateStart;
            $dateStartOrigine_datetime = new DateTime($task->dateStart);
            $dateEndOrigine_datetime   = new DateTime($task->dateEnd);
            $dateEnd_compare            = $dateEndOrigine_datetime->format('Y-m-d H');
            $dateStart_compare          = $dateStartOrigine_datetime->format('Y-m-d H');
            if ($task->reiterate > 0 && !($dateStartOrigine_datetime->format('Y-m-d') <= $date_compare && $date_compare <= $dateEndOrigine_datetime->format('Y-m-d')))
            {
                // Calcul de l'interval
                $diff     = $dateStartOrigine_datetime->diff($dateEndOrigine_datetime);
                $interval = $diff->format('%a');

                $dateStart                      = $task->getNewDate($task->dateStart, $task->reiterate, $task->interspace, 'last', $date);
                $dateStartOrigine_datetime_save = $dateStartOrigine_datetime      = new DateTime($dateStart);
                $dateStart_compare = $dateStartOrigine_datetime_save->format('Y-m-d H');
                
                $dateStartOrigine_datetime->add(new DateInterval('P' . $interval . 'D'));
                $dateEnd_compare                = $dateStartOrigine_datetime->format('Y-m-d H');
            }
            if ($dateStart_compare == $date_compare)
            {
                $performe       = 0;
                $performeSearch = Model::factory('performe')->loadOne(false, array('idTask' => $task->id, "created >= '$dateStart' and created <= '$dateEnd_compare'"));
                if ($performeSearch) {
                    $performe = 1;
                } elseif ($date > date('Y-m-d')) {
                    $performe = 2;
                }
                $data[] = array($task, $performe);
            }
        }
        return $data;
    }
    
    function dayAction()
    {
        $test = $this->_testDay();
        $tasks = Model::factory('task')->load(true, array('active = 1 and (reiterate != 0 OR (reiterate = 0 and date(dateStart) >= date(now())))'));
        
        $num_jour   = $this->getRequest()->getParam('jour', date("d"));
        $num_mois   = $this->getRequest()->getParam('mois', date("n"));
        $num_an   = $this->getRequest()->getParam('annee', date("Y"));
        $cal_task = array();
        for ($i = 0; $i <= 23; $i++)
        {
            $cal_task[$i] = $this->_calDay($i, $num_an, $num_mois, $num_jour, $tasks);
        }
        
        $this->view->num_jour = $num_jour;
        $this->view->num_mois = $num_mois;
        $this->view->num_an = $num_an;
        
    }

    function _testDay()
    {
        $data     = array('*' => array());
        $num_jour = $this->getRequest()->getParam('jour', date("d"));
        $num_mois = $this->getRequest()->getParam('mois', date("n"));
        $num_an   = $this->getRequest()->getParam('annee', date("Y"));
        $tasks    = Model::factory('task')->load(true, array('active = 1 and (reiterate != 0 OR (reiterate = 0 and date(dateStart) >= date(now())))'));
        
        for ($i = 0; $i <= 23; $i++) {
            $data[$i] = array();
        }
        
        if ($num_mois == 13)
        {
            $num_mois = 1;
            $num_an++;
        }
        
        $date_compare = $num_an . '-' . str_pad($num_mois, 2, 0, STR_PAD_LEFT) . '-' . str_pad($num_jour, 2, 0, STR_PAD_LEFT);
        foreach ($tasks as $task)
        {
            $dateStart          = $task->dateStart;
            $dateStart_datetime = new DateTime($task->dateStart);
            $dateEnd_datetime   = new DateTime($task->dateEnd);
            $dateEnd_compare    = $dateEnd_datetime->format('Y-m-d');
            $dateStart_compare  = $dateStart_datetime->format('Y-m-d');
            if ($task->reiterate > 0 && $dateStart_compare < $date_compare)
            {
                // Calcul de l'interval
                $diff     = $dateStart_datetime->diff($dateEnd_datetime);
                $interval = $diff->format('%a');

                $dateStart                      = $task->getNewDate($task->dateStart, $task->reiterate, $task->interspace, 'last', $date_compare);
                $dateStartOrigine_datetime_save = $dateStart_datetime             = new DateTime($dateStart);
                $dateStart_compare              = $dateStartOrigine_datetime_save->format('Y-m-d');

                $dateStart_datetime->add(new DateInterval('P' . $interval . 'D'));
                $dateEnd_compare = $dateStart_datetime->format('Y-m-d');
            }

            if ($dateStart_compare == $date_compare)
            {
                $performe       = 0;
                $performeSearch = Model::factory('performe')->loadOne(false, array('idTask' => $task->id, "created >= '$dateStart' and created <= '$dateEnd_compare'"));
                if ($performeSearch) {
                    $performe = 1;
                } elseif ($date_compare > date('Y-m-d')) {
                    $performe = 2;
                }
                $timeStart = $dateStart_datetime->format('H');
                $timeEnd = $dateEnd_datetime->format('H');
                $data[$dateStart_datetime->format('H')] = array($task, $performe);
            }
        }
        return $data;
    }

}
