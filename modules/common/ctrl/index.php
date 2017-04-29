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

echo $view->render($vars);