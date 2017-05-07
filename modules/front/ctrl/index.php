<?php

function task($vars)
{
    if (isset($_GET['id']))
    {
        $task                   = Task::factory('task')->loadOne(true, array('task.id' => $_GET['id']));
        $project_id             = $task->idProject;
        $task_name              = $task->name;
        $priority               = $task->priority;
        $dateStart_dateTime     = new DateTime($task->dateStart);
        $dateEnd_dateTime       = new DateTime($task->dateEnd);
        $dateStart              = $dateStart_dateTime->format('d/m/Y');
        $dateEnd                = $dateEnd_dateTime->format('d/m/Y');
        $timeStart              = $dateStart_dateTime->format('H:i');
        $timeEnd                = $dateEnd_dateTime->format('H:i');
        $repeat                 = $task->reiterate;
        $interspace             = $task->interspace;
        $reiterateEnd           = $task->reiterateEnd;
        if (isset($task->untilDate)) {
            $untilDate              = $task->untilDate;
        }
        $untilNumber            = $task->untilNumber;
    }
    $params = $_POST;
    if (isset($params['submit']))
    {
        if (!isset($params['task_name']) || empty($params['task_name'])) {
            echo '<span style="color:red">Le nom est obligatoire <br /></span>';
        } else {
            // Obligatoire
            $project_id     = $params['project_id'];
            $task_name      = $params['task_name'];
            $priority       = isset($params['priority']) ? $params['priority'] : 0;
            $dateStartSave  = $dateStart = BDD::dateFormat($params['dateStart']);
            $dateEndSave    = $dateEnd = BDD::dateFormat($params['dateEnd']);
            $timeStart      = $params['timeStart'];
            $timeEnd        = $params['timeEnd'];
            $repeat         = $params['repeat'];
            $interspace     = $params['interspace'];
            $reiterateEnd   = $params['reiterateEnd'];
            if (!empty($params['untilDate'])) {
                $untilDate      = $params['untilDate'];
            }
            $untilNumber    = $params['untilNumber'];

            if (!isset($task)) {
                $task = new Task();
            }

            if (isset($params['timeStart']) && !empty($params['timeStart'])) {
                $dateStartSave .= ' ' . $params['timeStart'] . ':00';
            } else {
                $dateStartSave .= ' 00:00:00';
            }
            if (isset($params['timeEnd']) && !empty($params['timeEnd'])) {
                $dateEndSave .= ' ' . $params['timeEnd'] . ':00';
            } else {
                $dateStartSave .= ' 00:00:00';
            }

            $task->setDateStart($dateStartSave);
            $task->setDateEnd($dateEndSave);
            $task->reiterate    = $repeat;
            $task->interspace   = $interspace;
            $task->reiterateEnd = $reiterateEnd;
            if (isset($untilDate)) {
                $task->untilDate    = $untilDate;
            }
            $task->untilNumber  = $untilNumber;
            $task->name    = $task_name;
            $task->priority     = $priority;
            $task->idProject    = $project_id;
            $task->save();
        }
    }
    $vars['projects']     = Project::factory('project')->load(false);
    $vars['task']         = (isset($task)) ? true : null;
    $vars['project_id']   = (isset($project_id)) ? $project_id : '';
    $vars['task_name']    = (isset($task_name)) ? $task_name : '';
    $vars['priority']     = (isset($priority)) ? $priority : '';
    $vars['dateStart']    = (isset($dateStart)) ? $dateStart : '';
    $vars['dateEnd']      = (isset($dateEnd)) ? $dateEnd : '';
    $vars['timeStart']    = (isset($timeStart)) ? $timeStart : '';
    $vars['timeEnd']      = (isset($timeEnd)) ? $timeEnd : '';
    $vars['repeat']       = (isset($repeat)) ? $repeat : '';
    $vars['interspace']   = (isset($interspace)) ? $interspace : '';
    $vars['reiterateEnd'] = (isset($reiterateEnd)) ? $reiterateEnd : '';
    $vars['untilDate']    = (isset($untilDate)) ? $untilDate : '';
    $vars['untilNumber']  = (isset($untilNumber)) ? $untilNumber : 0;
    
    return $vars;
}

function inbox($vars)
{
    include_once('modules/front/form/filtres.php');
    $filtres = new filtres();
    if (isset($_GET['filtre']) && $_GET['filtre'] == "today") {
        $vars['today'] = true;
        $vars = $filtres->check(array('filtrer' => true), $vars, false);
    } else {
        $vars = $filtres->check($_POST, $vars);
    }

    $tri = array();
    $where = array();
    $vars['priority'] = '11111';
    if (isset($_GET['priority'])) {
        $vars['priority'] = $_GET['priority'];
    }
    $where[] = '(reiterate != 0 OR (reiterate = 0 AND performe.id is null))';
    $tasks = BDD::factory('task')->loadInbox($vars['priority']);
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
    
    return $vars;
}

function del()
{
    BDD::factory('task')->delete(true, $_GET['id']); 
}

function done()
{
    $task = BDD::factory('task')->loadOne(false, array('id' => $_GET['id']));
    if (isset($task)) {
        $task->performe();
    } 
}

function cancel()
{
    BDD::factory('task')->deleteLastPerforme($_GET['id'], $_GET['idPerforme']); 
}