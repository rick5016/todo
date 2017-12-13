<?php

class Request
{

    public function isPost()
    {
        return ($_SERVER['REQUEST_METHOD'] == 'POST' ? true : false);
    }

    protected function _isGet()
    {
        return ($_SERVER['REQUEST_METHOD'] == 'GET' ? true : false);
    }

    public function getParam($key, $default = null)
    {
        if (isset($_GET[$key])) {
            return $_GET[$key];
        }

        if (isset($_POST[$key])) {
            return $_POST[$key];
        }

        return $default;
    }
    
    public function issetParam($key)
    {
        if ($this->isPost())
        {
            if (isset($_POST[$key])) {
                return true;
            }
        }
        else if ($this->_isGet())
        {
            if (isset($_GET[$key])) {
                return true;
            }
        }

        return false;
    }
    
    public function emptyParam($key)
    {
        if ($this->issetParam($key))
        {
            if ($this->isPost())
            {
                if (empty($_POST[$key])) {
                    return true;
                }
            }
            else if ($this->_isGet())
            {
                if (empty($_GET[$key])) {
                    return true;
                }
            }
        }

        return false;
    }

    public function getParams()
    {
        if ($this->isPost()) {
            return $_POST;
        } else if ($this->_isGet()) {
            return $_GET;
        }
    }
    
    public function getReferer()
    {
        return $_SERVER['HTTP_REFERER'];
    }

}
