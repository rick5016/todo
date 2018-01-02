<?php

class Model
{
    protected $dbh;

    function __construct($datas = array(), $foreign_keys = false, $className = '', $limit = 200)
    {
        $settings = parse_ini_file(ROOT_PATH . '/config/settings.ini', true);

        try
        {
            $this->dbh = new PDO($settings['database']['dsn'], $settings['database']['user'], $settings['database']['password']);
            $this->dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
            $this->dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }
        catch (PDOException $e) {
                echo 'Connexion échouée : ' . $e->getMessage();
        }
        if (!empty($datas)) {
            $this->populate($datas, $foreign_keys, $className, $limit);
        }
    }

    public static function init()
    {
        ORM_Autoloader::register();
    }
    
    protected function populate($datas = array(), $foreign_keys = false, $className = '', $limit = 200)
    {
        if (!empty($datas))
        {
            foreach ($this->attributs as $key => $attribut)
            {
                if (isset($datas[$this->bdd_name . '_' . $key])) {
                    $this->$key = $datas[$this->bdd_name . '_' . $key];
                }
                else
                {
                    if (isset($datas[$key])) {
                        $this->$key = $datas[$key];
                    }
                }
            }
            
            // Chargement des objets enfants Ã  l'aide des clÃ©s Ã©trangÃ¨res prÃ©sentent dans l'objet d'ORM
            if ($foreign_keys && (get_class($this) != $className))
            {
                // exemple : 'calendars' => array('calendar','id','idTask')
                foreach ($this->foreign_keys as $foreign_name => $foreign_datas)
                {
                    // Ne charge pas la class parente (permet d'Ã©viter les boucles infinies
                    // Charge l'objet enfant uniquement si l'id de cette enfant existe dans le tableau $datas
                    // TODO : si c'est le NiÃ¨me ($limit) object de la mÃªme classe on arrÃªte
                    if (strtolower($className) != 'orm_' . $foreign_datas[0] && isset($datas[$foreign_datas[0] . '_id']))
                    {
                        $foreign_obj = array();
                        $foreign_class_name = 'ORM_' . $foreign_datas[0];
                        $foreign_obj[] = new $foreign_class_name($datas, $foreign_keys, get_class($this));
                        $this->$foreign_name = $foreign_obj;
                    }
                }
            }
        }
    }
    
    function __call($function, $args)
    {
        switch (substr($function, 0, 3))
        {
            case 'set':
                $var        = strtolower(substr($function, 3, 1)) . substr($function, 4);
                $this->$var = $args[0];
                return $this;
            case 'get':
                $var        = strtolower(substr($function, 3, 1)) . substr($function, 4);
                if (isset($this->$var))
                    return $this->$var;
                else
                {
                    // TODO
                    return $this->loadRelation($var);
                }
            default:
                throw new Exception("Fonction $function invalide");
        }
    }
    
    // TODO
    private function loadRelation($var)
    {
//        $this->loadOne($id);
        return null;
    }
    
    private function execute($stmt)
    {
        if (!$return = $stmt->execute()) {
            throw new PDOException($stmt->errorInfo()[2]);
        }
        return $return;
    }
    
    /**
     * INSERT OR UPDATE
     * l'enregistrement des enfants (et les transactions) doivent Ãªtre gÃ©rÃ© dans les classe d'ORM respective
     * 
     * @return \BDD $this
     */
    function save()
    {
        if ($this->getId() != null && !empty($this->getId()))
        {
            $query1 = 'UPDATE ' . $this->bdd_name . ' SET';
            foreach ($this->attributs as $key => $attribut)
            {
                if (($key != 'created' && $key != 'updated' && $key != 'id') || ($key == 'id' && $this->getId() != null)) {
                    $query1 .= ' ' . $key . ' = :' . $key . ',';
                }
            }
            $query = substr($query1, 0, -1);
            $query .= " where id = " . $this->getId();

            $stmt  = $this->dbh->prepare($query);
            foreach ($this->attributs as $key => $attribut)
            {
                if (($key != 'created' && $key != 'updated' && $key != 'id') || ($key == 'id' && $this->getId() != null)) {
                    $stmt->bindValue(':' . $key, $this->{'get' . ucfirst($key)}());
                }
            }
            $this->execute($stmt);
        }
        else
        {
            $query1 = 'INSERT INTO ' . $this->bdd_name . '(';
            foreach ($this->attributs as $key => $attribut)
            {
                if (($key != 'created' && $key != 'updated' && $key != 'id') || ($key == 'id' && $this->getId() != null)) {
                    $query1 .= $key . ", ";
                }
            }
            $query2 = substr($query1, 0, -2);
            $query2 .= ") VALUES (";
            foreach ($this->attributs as $key => $attribut)
            {
                if (($key != 'created' && $key != 'updated' && $key != 'id') || ($key == 'id' && $this->getId() != null)) {
                    $query2 .= ':' . $key . ", ";
                }
            }
            $query = substr($query2, 0, -2);
            $query .= ")";

            $stmt  = $this->dbh->prepare($query);
            foreach ($this->attributs as $key => $attribut)
            {
                if (($key != 'created' && $key != 'updated' && $key != 'id') || ($key == 'id' && $this->getId() != null)) {
                    $stmt->bindValue(':' . $key, $this->{'get' . ucfirst($key)}());
                }
            }
//            "INSERT INTO task(idProject, name, priority, dateStart, dateEnd, reiterate, interspace, reiterateEnd, untilDate, untilNumber) "
//            . "VALUES (:idProject, :name, :priority, :dateStart, :dateEnd, :reiterate, :interspace, :reiterateEnd, :untilDate, :untilNumber)"
            $this->execute($stmt);
            if ($this->getId() == null) {
                $this->setId($this->dbh->lastInsertId());
            }
        }
        
        return $this;
    }
    
    /**
     * DELETE
     * TODO : gÃ©rer les suppressions des enfants rÃ©cursivement (comme le load)
     * 
     * @param booleen $foreing_keys
     * @param int $id
     * @throws PDOException
     */
    function delete($foreing_keys = false, $id = null)
    {
        try
        {
            if (!isset($id) && $this->getId() == null) {
                throw new Exception("L'objet n'existe pas en BDD");
            }
            if (!isset($id)) {
                $id = $this->getId();
            }

            $this->dbh->beginTransaction();
            
            if ($foreing_keys)
            {
                foreach ($this->foreign_keys as $datas)
                {
                    // 'calendars' => array('calendar','id','idTask')
                    $query = "DELETE from " . $datas[0] . " where " . $datas[2] . " = :id";
                    $stmt  = $this->dbh->prepare($query);
                    $stmt->bindValue(':id', $id);
                    $this->execute($stmt);
                }
            }

            $query = "DELETE from ".$this->bdd_name." where id = :id";
            $stmt  = $this->dbh->prepare($query);
            $stmt->bindValue(':id', $id);
            $this->execute($stmt);

            $this->dbh->commit();
        }
        catch (PDOException $e) {
            throw new Exception($e->getMessage());
        }
    }
    
    static function factory($name)
    {
        $className = 'ORM_' . $name;
        return new $className();
    }
    
    /**
     * RÃ©cupÃ¨re un rÃ©sulat sous forme de tableau d'objet avec ou sans enfants, peux prendre en paramÃ¨tre des clauses where sous forme de tableau ainsi qu'un order by sous forme de string
     * 
     * @param booleen $foreing_keys RÃ©cupÃ¨re ou non les objets enfants renseignÃ©s dans l'attribut foreing_keys sous forme de tableau d'objet
     * @param array $where Clause where sous forme de tableau clÃ© = valeur
     * @param string $orderby Order by sous forme de string
     * @return array(array(object BDD)) $result RÃ©sultat sous forme de teableau d'objet
     */
    function load($foreing_keys = false, $where = array(), $orderby = '')
    {
        try
        {
            $result = array();
            $query1 = "select";
            
            // Ajout des attributs de la class
            foreach ($this->attributs as $key => $attribut)
            {
                $query1 .= ' ' . $this->bdd_name . '.' . $key . ' as ' . $this->bdd_name . '_' . $key . ',';
            }
            
            // Ajout des attributs des classes enfants rÃ©cursivement
            if ($foreing_keys) {
                $query1 = $this->foreignKeysSelect($this, $query1);
            }
            $query = substr($query1, 0, -1);
            
            // Ajout du nom de la classe
            $query .= " from " . $this->bdd_name;
            
            // Ajout du nom des classes enfants ainsi que leurs relations
            if ($foreing_keys) {
                $query = $this->foreignKeysFrom($this, $query);
            }

            // Ajout de la clause where
            $nbWhere = count($where);
            if ($nbWhere > 0)
            {
                $query .= " where";
                foreach ($where as $key => $value)
                {
                    if (is_numeric($key)) {
                        $query .= ' ' . $value;
                    } else {
                        $query .= ' ' . $key . ' = ' . $value;
                    }
                    $nbWhere--;
                    if ($nbWhere > 0) {
                        $query .= ' and';
                    }
                }
            }

            // Ajout de l'order by
            if (!empty($orderby)) {
                $query .= " order by " . $orderby;
            }
            $stmt  = $this->dbh->query($query);
            if ($stmt) {
                while($data = $stmt->fetch())
                {
                    $className = 'ORM_' . $this->bdd_name;
                    $result[] = new $className($data, $foreing_keys);
                }  
            }
        }
        catch (PDOException $e) {
            throw new Exception($e->getMessage());
        }
        return $result;
    }
    
    /**
     * Permet de rÃ©cupÃ©rer le premier rÃ©sultat de la fonction load
     */
    function loadOne($foreing_keys = false, $where = array(), $orderby = '')
    {
        $result = $this->load($foreing_keys, $where, $orderby);

        if ($result)
            return $result[0];
        
        return false;
    }
    
    private function foreignKeysSelect($object, $result, $tableName = '')
    {
        foreach ($object->foreign_keys as $datas)
        {
            if ($datas[0] != $tableName)
            {
                $foreign_name = 'ORM_' . ucfirst($datas[0]);
                $foreign_obj = new $foreign_name();
                foreach ($foreign_obj->attributs as $key => $attribut)
                {
                    $result .= ' ' . $foreign_obj->bdd_name . '.' . $key . ' as ' . $foreign_obj->bdd_name . '_' . $key . ',';
                }
                $result = $object->foreignKeysSelect($foreign_obj, $result, $object->bdd_name);
            }
        }
        return $result;
    }
    
    private function foreignKeysFrom($object, $result, $tableName = '')
    {
        foreach ($object->foreign_keys as $datas)
        {
            if ($datas[0] != $tableName)
            {
                $foreign_name = 'ORM_' . ucfirst($datas[0]);
                $foreign_obj = new $foreign_name();
                $result .= " left join " . $datas[0] . " on " . $object->bdd_name . "." . $datas[1] . " = " . $datas[0] . "." . $datas[2];
                $result = $object->foreignKeysFrom($foreign_obj, $result, $object->bdd_name);
            }
        }
        return $result;
    }
    
    static function dateFormat($date)
    {
        if (strpos($date, '/'))
        {
            $arrayDateTime   = explode(' ', $date); // Time
            $arrayDate       = explode('/', $arrayDateTime[0]);
            if (isset($arrayDateTime[1])){
                return $arrayDate[2] . '-' . $arrayDate[1] . '-' . $arrayDate[0] . ' ' . $arrayDateTime[1];
            }
            else {
                return $arrayDate[2] . '-' . $arrayDate[1] . '-' . $arrayDate[0];
            }
        }
        return $date;
    }
}
