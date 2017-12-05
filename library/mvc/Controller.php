<?php

class Controller
{

    public $view        = null;
    protected $_request = null;

    public function init()
    {
        $this->view = new View();
        Model::init();
        Form::init();
    }

    public function execute($action = 'index', $module = 'common')
    {
        $this->init();
        if (method_exists($this, $action . 'Action'))
        {
            $this->{$action . 'Action'}();
        }
        else
        {
            throw new Exception($action . 'Action n\'existe pas');
        }
        
        echo $this->view->renderViewScript($module . '/views/' . $action . '.tpl');
    }

    public function getRequest()
    {
        if ($this->_request == null) {
            $this->_request = new Request();
        }

        return $this->_request;
    }

    protected function _getParam($key, $default = null)
    {
        // tests against the named parameters first
        if (isset($this->_namedParameters[$key]))
        {
            return $this->_namedParameters[$key];
        }

        // tests against the GET/POST parameters
        return $this->getRequest()->getParam($key, $default);
    }

    /**
     * Fetches all the current parameters
     * @return array a list of all the parameters
     */
    protected function _getAllParams()
    {
        return array_merge($this->getRequest()->getAllParams(), $this->_namedParameters);
    }

    public function addNamedParameter($key, $value)
    {
        $this->_namedParameters[$key] = $value;
    }

}
