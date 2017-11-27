<?php

/**
 * A controller used for handling standard errors
 * @author jimmiw
 * @since 2012-06-27
 */
class ErrorController extends Controller
{

    protected $_exception = null;

    /**
     * Sets the exception to show information about
     */
    public function setException(Exception $exception)
    {
        $this->_exception = $exception;
    }

    /**
     * The error action, which is called whenever there is an error on the site
     */
    public function errorAction()
    {
        // sets the 404 header
        header("HTTP/1.0 404 Not Found");

        // sets the error to be rendered in the view
        echo $this->_exception->getMessage() . '<br />';
        echo $this->_exception->xdebug_message;
        var_dump($this->_exception);

        // logs the error to the log
        error_log($this->view->error);
        error_log($this->_exception->getTraceAsString());
        exit;
    }

}
