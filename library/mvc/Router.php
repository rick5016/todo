<?php

class Router
{

    private $_routes;

    function __construct($routes)
    {
        $this->_routes = $routes;
    }

    public function execute()
    {
        try
        {
            $routeFound = $this->_getSimpleRoute();
            
            if ($routeFound === false)
            {
                throw new Exception('no route added for ' . $_SERVER['REQUEST_URI']);
            }
        }
        catch (Exception $exception)
        {
            $controller = new ErrorController();
            $controller->setException($exception);
            $controller->execute('error');
        }
    }

    private function _getUri()
    {
        $uriTab = explode('?', $_SERVER['REQUEST_URI']);
        
        return substr($uriTab[0], strlen(WEB_ROOT));
    }

    protected function _getSimpleRoute()
    {
        $uri = $this->_getUri();

        if (isset($this->_routes[$uri])) {
            $routeFound = $this->_routes[$uri];
        }

        if (isset($routeFound))
        {
            $moduleCtrlAction = array_reverse(explode('/', $routeFound));
            $ctrlAction       = array_reverse(explode('#', $moduleCtrlAction[0]));
            $module           = (isset($moduleCtrlAction[1])) ? $moduleCtrlAction[1] : 'common';
            $ctrl             = (isset($ctrlAction[1])) ? $ctrlAction[1] : 'index';
            $action           = (!empty($ctrlAction[0])) ? $ctrlAction[0] : 'index';

            $controllerName = (strtolower($module) != 'common') ? ucfirst($module) . '_' : '';
            $controllerName .= ucfirst($ctrl) . 'Controller';

            $controller = new $controllerName();
            $controller->execute($action, $module);
            
            return true;
        
        }
        else {
            return false;
        }
    }

}
