<?php

class Router
{

    private $_routes;
    private $_action;
    private $_ctrl;
    private $_ctrlName;
    private $_module;
    public $uri;

    function __construct($routes)
    {
        $this->_routes   = $routes;
        $this->getUri();

        Model::init();
        Form::init();
    }

    public function execute()
    {
        try
        {
            $routeFound = $this->setRoute();

            if ($routeFound === false) {
                throw new Exception('no route added for ' . $_SERVER['REQUEST_URI']);
            }
            $this->performeRoute();
        }
        catch (Exception $exception)
        {
            $controller = new ErrorController();
            $controller->setException($exception);
            $controller->execute('error');
        }
    }

    public function getUri()
    {
        $uriTab    = explode('?', $_SERVER['REQUEST_URI']);
        $this->uri = substr($uriTab[0], strlen(WEB_ROOT));
    }

    protected function setRoute()
    {
        if (isset($this->_routes[$this->uri]))
        {
            $routeFound = $this->_routes[$this->uri];
        }

        if (isset($routeFound))
        {
            $moduleCtrlAction = array_reverse(explode('/', $routeFound));
            $ctrlAction       = array_reverse(explode('#', $moduleCtrlAction[0]));
            $this->_module    = (isset($moduleCtrlAction[1])) ? $moduleCtrlAction[1] : 'common';
            $this->_ctrl      = (isset($ctrlAction[1])) ? $ctrlAction[1] : 'index';
            $this->_action    = (!empty($ctrlAction[0])) ? $ctrlAction[0] : 'index';

            $this->_ctrlName = (strtolower($this->_module) != 'common') ? ucfirst($this->_module) . '_' : '';
            $this->_ctrlName .= ucfirst($this->_ctrl) . 'Controller';

            return true;
        }

        return false;
    }

    protected function performeRoute()
    {
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']))
        {
            // ajax
            $controller      = new $this->_ctrlName(true);
            $controller->execute($this->_action);

            return $controller->renderViewScriptAjax($this->_action, $this->_ctrl, $this->_module);
        }
        else
        {

            // Variables du template
            $controllerFront  = new IndexController();
            
            $controller = new $this->_ctrlName();
            $controller->execute($this->_action);

            $controllerFront->indexAction();
            $controller->view->add($controllerFront->view->parameters + array('uri' => $this->uri));

            $controller->renderViewScript($this->_action, $this->_ctrl, $this->_module);
        }
    }

}
