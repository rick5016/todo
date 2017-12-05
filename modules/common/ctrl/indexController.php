<?php

class IndexController extends Controller
{

    function indexAction()
    {
        $this->view->projects = Model::factory('project')->load(false);
    }

}
