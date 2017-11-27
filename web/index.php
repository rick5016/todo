<?php

error_reporting(E_ALL & ~ E_NOTICE);
ini_set('display_errors', 1);
date_default_timezone_set('CET');

$scriptName = $_SERVER['SCRIPT_NAME'];
// defines the web root
define('WEB_ROOT', substr($_SERVER['SCRIPT_NAME'], 0, strpos($_SERVER['SCRIPT_NAME'], '/index.php')));
// defindes the path to the files
define('ROOT_PATH', realpath(dirname(__FILE__) . '/../'));

// starts the session
session_start();

// includes the system routes. Define your own routes in this file
include(ROOT_PATH . '/config/routes.php');

function autoloader($className)
{
    $modules = array('common', 'front');
    if ($className == 'Twig_Autoloader') // Autoloader de Twig
    {
        require_once ROOT_PATH . '/library/twig/Autoloader.php';
    }
    elseif (substr($className, 0, 4) != 'Twig') // Autoloader Non Twig
    {
        if (strlen($className) > 10 && substr($className, -10) == 'Controller') // Controller
        {
            $ctrl = array_reverse(explode('_', $className));
            foreach ($modules as $module)
            {
                if (file_exists(ROOT_PATH . '/modules/' . $module . '/ctrl/' . $ctrl[0] . '.php')) {
                    require_once ROOT_PATH . '/modules/' . $module . '/ctrl/' . $ctrl[0] . '.php';
                }
            }
        }
        elseif (substr($className, 0, 4) == 'Form' || $className == 'form')
        {
            $form = array_reverse(explode('_', $className));
            foreach ($modules as $module)
            {
                if (file_exists(ROOT_PATH . '/modules/' . $module . '/form/' . $form[0] . '.php')) {
                    require_once ROOT_PATH . '/modules/' . $module . '/form/' . $form[0] . '.php';
                }
            }
        }
        else
        {
            if (file_exists(ROOT_PATH . '/library/mvc/' . $className . '.php')) { // MVC
                require_once ROOT_PATH . '/library/mvc/' . $className . '.php';
            } else { // modeles
                require_once ROOT_PATH . '/modeles/' . $className . '.php';
            }
        }
    }
}

// activates the autoloader
spl_autoload_register('autoloader');

$router = new Router($routes);
$router->execute();
