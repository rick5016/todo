<?php

function task($vars)
{
    if (isset($_GET['id']))
    {
        $id                     = array('id' => $_GET['id']);
        $task                   = Task::factory('task')->loadOne(true, array('task.id' => $_GET['id']));
        $name                   = $task->name;
        $priority               = $task->priority;
        $calendar               = $task->calendars[0];
        $dateStart_dateTime     = new DateTime($calendar->dateStart);
        $dateEnd_dateTime       = new DateTime($calendar->dateEnd);
        $dateStart              = $dateStart_dateTime->format('d/m/Y');
        $dateEnd                = $dateEnd_dateTime->format('d/m/Y');
        $timeStart              = $dateStart_dateTime->format('H:i');
        $timeEnd                = $dateEnd_dateTime->format('H:i');
        $repeat                 = $calendar->reiterate;
        $interspace             = $calendar->interspace;
        $reiterateEnd           = $calendar->reiterateEnd;
        if (isset($calendar->untilDate)) {
            $untilDate              = $calendar->untilDate;
        }
        $untilNumber            = $calendar->untilNumber;
    }

    if (isset($_POST['submit']))
    {
        if (!isset($_POST['name']) || empty($_POST['name'])) {
            echo '<span style="color:red">Le nom est obligatoire <br /></span>';
        } else {
            // Obligatoire
            $name           = $_POST['name'];
            $priority       = isset($_POST['priority']) ? $_POST['priority'] : 0;
            $dateStartSave  = $dateStart = BDD::dateFormat($_POST['dateStart']);
            $dateEndSave    = $dateEnd = BDD::dateFormat($_POST['dateEnd']);
            $timeStart      = $_POST['timeStart'];
            $timeEnd        = $_POST['timeEnd'];
            $repeat         = $_POST['repeat'];
            $interspace     = $_POST['interspace'];
            $reiterateEnd   = $_POST['reiterateEnd'];
            if (!empty($_POST['untilDate'])) {
                $untilDate      = $_POST['untilDate'];
            }
            $untilNumber    = $_POST['untilNumber'];

            if (!isset($task) && !isset($calendar))
            {
                $calendar = new Calendar();
                $task = new Task();
            }

            if (isset($_POST['timeStart']) && !empty($_POST['timeStart'])) {
                $dateStartSave .= ' ' . $_POST['timeStart'] . ':00';
            } else {
                $dateStartSave .= ' 00:00:00';
            }
            if (isset($_POST['timeEnd']) && !empty($_POST['timeEnd'])) {
                $dateEndSave .= ' ' . $_POST['timeEnd'] . ':00';
            } else {
                $dateStartSave .= ' 00:00:00';
            }

            $calendar->setDateStart($dateStartSave);
            $calendar->setDateEnd($dateEndSave);
            $calendar->reiterate    = $repeat;
            $calendar->interspace   = $interspace;
            $calendar->reiterateEnd = $reiterateEnd;
            if (isset($untilDate)) {
                $calendar->untilDate    = $untilDate;
            }
            $calendar->untilNumber  = $untilNumber;
            $task->name             = $name;
            $task->priority         = $priority;
            $task->calandar         = array($calendar);
            $task->save();
        }
    }
    $vars['calendar']     = (isset($calendar)) ? true : null;
    $vars['name']         = (isset($name)) ? $name : '';
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

    $returns = array();
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
        
        $calendar               = $task->calendars[0];

        // Afficher ou non les dates après Aujourd'hui
        if (!isset($vars['ant']) && $calendar->dateStart > date('Y-m-d')) {
            $afficher_la_tache = false;
        }
        
        if ($afficher_la_tache)
        {
            $date_affichage = $calendar->dateStart;
            
            // Si la date d'aujoud'hui ce situe entre le début et la fin de l'événement alors la date d'affiche est la date d'aujourd'hui
            // Si la date de fin est passé et que retierate <> de 0 alors la date d'affiche est la date d'aujourd'hui
            if (($date_affichage < date('Y-m-d') && $calendar->dateEnd >  date('Y-m-d')) || ($calendar->dateEnd <  date('Y-m-d') && $calendar->reiterate != 0)) {
                $date_affichage = date('Y-m-d') . ' 00:00:00';
            }
            $calendar->dateAffichage = $date_affichage;
            
            // Afficher ou non les dates avant aujourd'hui
            if (!isset($vars['past']) && ($calendar->dateAffichage < date('Y-m-d'))) {
                $afficher_la_tache = false;
            }
            
            if ($afficher_la_tache) {
                $returns[$date_affichage . '-' . $task->priority . '-' . $calendar->id][] = $task;
            }
        }
    }
    ksort($returns);
    $return = array();
    foreach ($returns as $datas)
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
    $calendar = BDD::factory('calendar')->loadOne(false, array('id' => $_GET['id']));
    if (isset($calendar)) {
        $calendar->updatePerforme();
    } 
}

function setDateNext($date, $type, $interval)
{
    $today = date('Y-m-d');
    if ($date <= $today)
    {
        while ($date <= $today)
        {
            $dateTime = new DateTime($date);
            if ($type == 'day') {
                $dateTime->add(new DateInterval('P' . $interval . 'D'));
            } elseif ($type == 'week')
            {
                $interval *= 7;
                $dateTime->add(new DateInterval('P' . $interval . 'D'));
            } 
            elseif ($type == 'month') {
                $dateTime->add(new DateInterval('P' . $interval . 'M'));
            } elseif ($type == 'year') {
                $dateTime->add(new DateInterval('P' . $interval . 'Y'));
            }
            $date = $dateTime->format('Y-m-d');
        }
    }
    return $date;
}
function setDatePrev($date, $type, $interval)
{
    $today = date('Y-m-d');
    if ($date < $today)
    {
        while ($date < $today)
        {
            $dateprev = $date;
            $dateTime = new DateTime($date);
            if ($type == 'day') {
                $dateTime->add(new DateInterval('P' . $interval . 'D'));
            } elseif ($type == 'week')
            {
                $interval *= 7;
                $dateTime->add(new DateInterval('P' . $interval . 'D'));
            }
            elseif ($type == 'month') {
                $dateTime->add(new DateInterval('P' . $interval . 'M'));
            } elseif ($type == 'year') {
                $dateTime->add(new DateInterval('P' . $interval . 'Y'));
            }
            $date = $dateTime->format('Y-m-d');
        }
    }
    return isset($dateprev) ? $dateprev : $date;
}