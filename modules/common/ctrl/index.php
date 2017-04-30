<?php

Twig_Autoloader::register();
$twig = new Twig_Environment( new Twig_Loader_Filesystem("./modules"),
    array( "cache" => false ) );
    //array( "cache" => "./temp/cache" ) );
$twig->addGlobal('app', new Twigplugin());
$vars = array();
$view = $twig->load("/common/views/template.tpl");
if (isset($_GET['page'])) {
    $vars['page'] = $_GET['page'];
}
if (isset($_GET['id'])) {
    $vars['id'] = $_GET['id'];
}

include_once("modules/front/ctrl/index.php");

if (isset($_GET['page']))
{
    $func = $_GET['page'];
    if ($_GET['page'] == "task")
    {
        $vars = $func($vars);
    }
    elseif ($_GET['page'] == "inbox")
    {
        $vars = $func($vars);
    }
    elseif ($_GET['page'] == "del" && isset($_GET['id']))
    {
        $func(); 
    }
    elseif ($_GET['page'] == "done" && isset($_GET['id']))
    {
        $func(); 
    }
}
echo $view->render($vars);