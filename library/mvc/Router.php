<?php

class Router
{

    private $_routes;
    public $uri;

    function __construct($routes)
    {
        $this->_routes = $routes;
        $this->getUri();

        Model::init();
        Form::init();
    }

    public function execute()
    {
        try
        {
            if (!isset($this->_routes[$this->uri])) {
                throw new Exception('no route added for ' . $_SERVER['REQUEST_URI']);
            }

            $moduleCtrlAction = array_reverse(explode('/', $this->_routes[$this->uri])); // array(actionName&ctrlName, module)
            $ctrlAction       = array_reverse(explode('#', $moduleCtrlAction[0])); // arrray(actionName, ctrlName)

            $ctrlName = (strtolower($moduleCtrlAction[1]) != 'common') ? ucfirst($moduleCtrlAction[1]) . '_' : '';
            $ctrlName .= ucfirst($ctrlAction[1]) . 'Controller';

            if ($ctrlAction[0] == 'index' && $ctrlAction[1] == 'index' && $moduleCtrlAction[1] == 'common') {
                header('Location: http://' . $_SERVER['SERVER_NAME'] . ROOT_ACCUEIL);
            } else {
                $controller = new $ctrlName($ctrlAction[0], $ctrlAction[1], $moduleCtrlAction[1]);
            }
            
            $controller->execute();
        }
        catch (Exception $exception)
        {
            $controller = new ErrorController('error');
            $controller->setException($exception);
            $controller->execute();
        }
    }

    public function getUri()
    {
        $uriTab    = explode('?', $_SERVER['REQUEST_URI']);
        $this->uri = substr($uriTab[0], strlen(WEB_ROOT));
    }
}
