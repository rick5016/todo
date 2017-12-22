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
        $t         = 1;
        $p         = "";

        $this->view->num_an    = $num_an;
        $this->view->num_mois  = $num_mois;
        $this->view->tab_mois  = $tab_mois;
        $this->view->tab_jours = $tab_jours;

        for ($i = 0; $i < 6; $i++)
        {
            for ($j = 0; $j < 7; $j++)
            {
                if ($j + 1 == $int_premj && $t == 1) // on stocke le premier jour du mois
                {
                    $tab_cal[$i][$j] = $this->_calDay($p . $t, $num_an, $num_mois, $t, $tasks);
                    $t++;
                }
                elseif ($t > 1 && $t <= $int_nbj) // on incremente a chaque fois...
                {
                    $tab_cal[$i][$j] = $this->_calDay($p . $t, $num_an, $num_mois, $t, $tasks);
                    $t++;
                }
                elseif ($t > $int_nbj) // on a mis tout les numeros de ce mois, on commence a mettre ceux du suivant
                {
                    $t               = 1;
                    $num_mois++;
                    $p               = "*";
                    $tab_cal[$i][$j] = $this->_calDay($p . $t, $num_an, $num_mois, $t, $tasks);
                    $t++;
                }
                elseif ($t == 1) // on a pas encore mis les num du mois, on met ceux de celui d'avant
                {
                    $tab_cal[$i][$j] = $this->_calDay("*" . ($int_nbjAV - ($int_premj - ($j + 1)) + 1), $num_an, $num_mois, $t, $tasks);
                }
            }
        }

        $this->view->tab_cal = $tab_cal;
    }

    function _calDay($day, $num_an, $num_mois, $t, $tasks)
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

}
