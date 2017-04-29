<?php

if (isset($_GET['page']))
{
    if ($_GET['page'] == "task")
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
            $dateStart              = $dateStart_dateTime->format('m/d/Y');
            $dateEnd                = $dateEnd_dateTime->format('m/d/Y');
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
                    $calendar->setDateCreateCalendar($dateStartSave);
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
        $vars['untilNumber']  = (isset($untilNumber)) ? $untilNumber : '';
    }
    elseif ($_GET['page'] == "inbox")
    {
        $voir_tache_ancienne    = false;
        $voir_tache_effectue    = false;
        $voir_tache_futur       = false;
        if (isset($_POST['past']))
        {
            $vars['past'] = true;
            $voir_tache_ancienne    = true;
        } 
        if (isset($_POST['end']))
        {
            $vars['end'] = true;
            $voir_tache_effectue    = true;
        }
        if (isset($_POST['ant']))
        {
            $vars['ant'] = true;
            $voir_tache_futur       = true;
        }
        
        $returns = array();
        $where = array();
        if (isset($_GET['priority'])) {
            $where = array('priority' => $_GET['priority']);
        }
        $where[] = '(reiterate != 0 OR (reiterate = 0 AND performe.id is null))';
//        $vars['tasks'] = BDD::factory('task')->load(true, $where, 'dateStart, priority');
        $tasks = BDD::factory('task')->loadInbox(true, $where, 'dateStart, priority');
        foreach ($tasks as $task)
        {
            $afficher_la_tache = true;
            $calendar = $task->calendars[0];
            $calendar->done = false;
            $date = new DateTime($calendar->dateStart);
            $calendar->dateStart = $date->format('Y-m-d');
            $performes = $calendar->getPerformes();
            
            if ($calendar->reiterate == 1)
            {
                $calendar->dateStart = date('Y-m-d');
                if (isset($performes))
                {
                    $performe = $performes[0];
                    $performe_dateStart = $performe->dateUpdate;
                    if ($performe_dateStart >= date('Y-m-d'))
                    {
                        // TODO : cacher les taches effectuées
                        if (!$voir_tache_effectue) {
                            $afficher_la_tache = false;
                        }
                        $calendar->done     = true;
                        
                        // TODO : cacher les taches dans le futur
                        if ($voir_tache_futur)
                        {
                            $taskNew                            =  clone $task;
                            $taskNew->name                      = 'test_new';
                            $calendarNew                        = new Calendar();
                            $calendarNew->id                    = $calendar->id;
                            $calendarNew->idtask                = $calendar->idtask;
                            $calendarNew->dateCreateCalendar    = $calendar->dateCreateCalendar;
                            $calendarNew->dateStart             = $calendar->dateStart;
                            $calendarNew->dateEnd               = $calendar->dateEnd;
                            $calendarNew->reiterate             = $calendar->reiterate;
                            $calendarNew->dateStart             = setDateNext($calendarNew->dateStart, 'day');
                            $calendarNew->done                  = false;
                            $taskNew->calendars                 = array($calendarNew);
                            $returns[$calendarNew->dateStart . '-' . $task->priority . '-' . $calendar->id][] = $taskNew;
                        }
                    }
                }
            }
            
            if ($calendar->reiterate == 2)
            {
                $calendar->dateStart = setDatePrev($calendar->dateStart, 'week');
                if (isset($performes))
                {
                    $performe = $performes[0];
                    $performe_dateStart = $performe->dateUpdate;
                    if ($performe_dateStart >= date('Y-m-d'))
                    {
                        $calendar->done = true;
                        $calendar->dateStart = setDateNext($calendar->dateStart, 'week');
                        $tasks[] = $calendar;
                    }
                }
            }
            
            if ($calendar->reiterate == 3)
            {
                $calendar->dateStart = setDatePrev($calendar->dateStart, 'month');
                if (isset($performes))
                {
                    $performe = $performes[0];
                    $performe_dateStart = $performe->dateUpdate;
                    if ($performe_dateStart >= date('Y-m-d'))
                    {
                        $calendar->done = true;
                        $calendar->dateStart = setDateNext($calendar->dateStart, 'month');
                    }
                }
            }
            if (!$voir_tache_ancienne && ($calendar->dateStart < date('Y-m-d'))) {
                $afficher_la_tache = false;
            }
            if ($afficher_la_tache) {
                $returns[$calendar->dateStart . '-' . $task->priority . '-' . $calendar->id][] = $task;
            }
        }
        ksort($returns);
        $return = array();
        foreach ($returns as $datas)
        {
            foreach ($datas as $data)
            {
                $return[] = $data;
            }
        }
        
        $vars['tasks'] = $return;
    }
    elseif ($_GET['page'] == "del" && isset($_GET['id']))
    {
        BDD::factory('task')->delete(true, $_GET['id']);
    }
    elseif ($_GET['page'] == "done" && isset($_GET['id']))
    {
        $calendar = BDD::factory('calendar')->loadOne(false, array('id' => $_GET['id']));
        if (isset($calendar)) {
            $calendar->updatePerforme();
        }
    }
}
    function setDateNext($date, $type)
    {
        $today = date('Y-m-d');
        if ($date <= $today)
        {
            while ($date <= $today)
            {
                $dateTime = new DateTime($date);
                if ($type == 'day') {
                    $dateTime->add(new DateInterval('P1D'));
                } elseif ($type == 'week') {
                    $dateTime->add(new DateInterval('P7D'));
                } elseif ($type == 'month') {
                    $dateTime->add(new DateInterval('P1M'));
                } elseif ($type == 'year') {
                    $dateTime->add(new DateInterval('P1Y'));
                }
                $date = $dateTime->format('Y-m-d');
            }
        }
        return $date;
    }
    function setDatePrev($date, $type)
    {
        $today = date('Y-m-d');
        if ($date < $today)
        {
            while ($date < $today)
            {
                $dateprev = $date;
                $dateTime = new DateTime($date);
                if ($type == 'day') {
                    $dateTime->add(new DateInterval('P1D'));
                } elseif ($type == 'week') {
                    $dateTime->add(new DateInterval('P7D'));
                } elseif ($type == 'month') {
                    $dateTime->add(new DateInterval('P1M'));
                } elseif ($type == 'year') {
                    $dateTime->add(new DateInterval('P1Y'));
                }
                $date = $dateTime->format('Y-m-d');
            }
        }
        return isset($dateprev) ? $dateprev : $date;
    }