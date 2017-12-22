<?php

class View
{

    public $twig;
    public $view;
    public $parameters = array();

    public function __construct($ajax = false)
    {
        $cache = false;
//        $cache = '../temp/views';
        
        Twig_Autoloader::register();
        $this->twig = new Twig_Environment(new Twig_Loader_Filesystem("../modules"), array("cache" => $cache));
        $this->twig->addGlobal('app', new Plugin_Form());
        
        if (!$ajax) {
            $this->view = $this->twig->load("/common/views/template.tpl");
        }
    }
    
    public function renderViewScript($path)
    {
        $viewScript = $this->twig->load($path);
        $content    = $viewScript->render(array('content' => ob_get_clean()) + $this->parameters);

        return $this->view->render(array('content' => $content) + $this->parameters);
    }
    
    public function renderViewScriptAjax()
    {
        return json_encode($this->view->render($this->parameters));
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
    
    public function add(array $params)
    {
        $this->parameters += $params;
    }
}
