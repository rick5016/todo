<?php

class View
{

    public $twig;
    public $view;
    public $parameters = array();
    

    public function __construct()
    {
        Twig_Autoloader::register();
        $this->twig = new Twig_Environment(new Twig_Loader_Filesystem("../modules"), array("cache" => false));
        $this->twig->addGlobal('app', new Plugin_Form());
        $this->view = $this->twig->load("/common/views/template.tpl");
    }
    
    public function renderViewScript($path)
    {
        $viewScript = $this->twig->load($path);
        $content    = $viewScript->render(array('content' => ob_get_clean()) + $this->parameters);

        return $this->view->render(array('content' => $content) + $this->parameters);
    }
    
    public function __set($name, $arguments)
    {
        $this->parameters[$name] = $arguments;
    }

    public function __get($name)
    {
        if (isset($this->parameters[$name])) {
            return $this->parameters[$name];
        }
        
        return null;
    }
}
