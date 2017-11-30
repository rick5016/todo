<?php

class ORM_Autoloader
{
    
    public static function register($prepend = false)
    {
        if (PHP_VERSION_ID < 50300) {
            spl_autoload_register(array(__CLASS__, 'autoload'));
        } else {
            spl_autoload_register(array(__CLASS__, 'autoload'), true, $prepend);
        }
    }

    public static function autoload($class)
    {
        if (0 !== strpos($class, 'ORM') && 0 !== strpos($class, 'Repository')) {
            return;
        }

        if (0 === strpos($class, 'ORM'))
        {
            $className = array_reverse(explode('_', $class));
            if (file_exists(ROOT_PATH . '/modeles/' . $className[0] . '.php')) {
                require_once ROOT_PATH . '/modeles/' . $className[0] . '.php';
            }
        }

        if (0 === strpos($class, 'Repository'))
        {
            $className = array_reverse(explode('_', $class));
            if (file_exists(ROOT_PATH . '/modeles/repository/' . $className[0] . '.php')) {
                require_once ROOT_PATH . '/modeles/repository/' . $className[0] . '.php';
            }
        }
    }

}
