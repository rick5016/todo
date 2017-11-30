<?php

class ORM_Repository
{
    
    static function factory($name)
    {
        $repository = 'Repository_' . $name;
        return new $repository();
    }
    
    public function load()
    {
        
    }
    
    public function loadOne()
    {
        
    }

}
