<?php

class BDD
{
    protected static $instance    = null;
    protected $dbh;

    function __construct()
    {
        $settings = parse_ini_file(ROOT_PATH . '/config/settings.ini', true);

        try
        {
            $this->dbh = new PDO($settings['database']['dsn'], $settings['database']['user'], $settings['database']['password']);
            $this->dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
            $this->dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }
        catch (PDOException $e) {
            throw new Exception('Connexion échouée : ' . $e->getMessage());
        }
    }
    
    public static function getConnection()
    {
        if (self::$instance == null) {
            self::$instance = new BDD();
        }

        return self::$instance->dbh;
    }

}
