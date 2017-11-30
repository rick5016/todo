<?php

class Form_Autoloader
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
        if (0 !== strpos($class, 'Form') && 0 !== strpos($class, 'Element')) {
            return;
        }

        if (0 === strpos($class, 'Form'))
        {
            $className = array_reverse(explode('_', $class));
            if (file_exists(ROOT_PATH . '/form/' . $className[0] . '.php')) {
                require_once ROOT_PATH . '/form/' . $className[0] . '.php';
            }
        }

        if (0 === strpos($class, 'Element'))
        {
            $className = array_reverse(explode('_', $class));
            if (file_exists(ROOT_PATH . '/form/elements/' . $className[0] . '.php')) {
                require_once ROOT_PATH . '/form/elements/' . $className[0] . '.php';
            }
        }
    }

}
