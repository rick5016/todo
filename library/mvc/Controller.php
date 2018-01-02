<?php

class Controller
{

    public $action;
    public $ctrl;
    public $module;
    public $view        = null;
    protected $_request = null;
    public $template    = true;

    public function __construct($action = 'index', $ctrl = 'index', $module = 'common')
    {
        $this->action = $action;
        $this->ctrl   = $ctrl;
        $this->module = $module;
        $this->view   = new View();
    }

    public function getRequest()
    {
        if ($this->_request == null) {
            $this->_request = new Request();
        }

        return $this->_request;
    }

    public function execute()
    {
        if (!method_exists($this, $this->action . 'Action')) {
            throw new Exception($this->action . 'Action n\'existe pas');
        }

        $this->{$this->action . 'Action'}();

        if (!($this->action == 'index' && $this->ctrl == 'index' && $this->module == 'common') && $this->template && empty($_SERVER['HTTP_X_REQUESTED_WITH']))
        {
            $controllerFront = new IndexController();
            $controllerFront->indexAction();
            $this->view->add($controllerFront->view->parameters);
        }

        $this->view->renderViewScript($this->action, $this->ctrl, $this->module, $this->template);
    }
}
