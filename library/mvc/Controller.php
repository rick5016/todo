<?php

class Controller
{

    public $view        = null;
    protected $_request = null;
    public $uri;

    public function __construct($ajax = false)
    {
        $this->view = new View($ajax);
    }

    public function execute($action = 'index')
    {
        if (method_exists($this, $action . 'Action'))
        {
            $this->{$action . 'Action'}();
        }
        else
        {
            throw new Exception($action . 'Action n\'existe pas');
        }
    }
    
    public function renderViewScript($action = 'index', $ctrl = 'index', $module = 'common')
    {
        if (file_exists(ROOT_PATH . '/modules/' . $module . '/views/' . $ctrl . '#' . $action . '.tpl')) {
            echo $this->view->renderViewScript($module . '/views/' . $ctrl . '#' . $action . '.tpl');
        } else {
            echo $this->view->renderViewScript($module . '/views/' . $action . '.tpl');
        } 
    }
    
    public function renderViewScriptAjax($action = 'index', $ctrl = 'index', $module = 'common')
    {
        if (file_exists(ROOT_PATH . '/modules/' . $module . '/views/' . $ctrl . '#' . $action . '.tpl')) {
            $this->view->view = $this->view->twig->load($module . '/views/' . $ctrl . '#' . $action . '.tpl');
        } else {
            $this->view->view = $this->view->twig->load($module . '/views/' . $action . '.tpl');
        }
        echo $this->view->renderViewScriptAjax();
    }

    public function getRequest()
    {
        if ($this->_request == null) {
            $this->_request = new Request();
        }

        return $this->_request;
    }

}
