<?php

class Plugin_Form
{
    function test($form, $elementName)
    {
        return 'testfsdfsd';
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
}
