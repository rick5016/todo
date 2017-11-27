<?php

class View
{

    public $twig;
    public $view;
    

    public function __construct()
    {
        Twig_Autoloader::register();
        $this->twig = new Twig_Environment(new Twig_Loader_Filesystem("../modules"), array("cache" => false));

        $this->view = $this->twig->load("/common/views/template.tpl");
    }
    
    public function renderViewScript($path, $parameters)
    {
        $viewScript = $this->twig->load($path);
        $content    = $viewScript->render(array('content' => ob_get_clean()) + $parameters);

        return $this->view->render(array('content' => $content) + $parameters);
    }

}
