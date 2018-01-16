<?php

class Model
{

    protected $dbh;
    private $query  = '';
    private $select = array();
    private $join   = array();
    private $where  = array();

    function __construct($datas = array(), $foreign_keys = false, $className = '')
    {
        $this->dbh   = BDD::getConnection();

        if (!empty($datas)) {
            $this->populate($datas, $foreign_keys, $className);
        }
    }

    public static function init()
    {
        ORM_Autoloader::register();
    }
    
    static function factory($name)
    {
        $className = 'ORM_' . $name;
        return new $className();
    }
    
    protected function populate($datas = array(), $foreign_keys = false, $className = '')
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
                    if (count($foreign_datas) != 2)
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
                if (isset($this->$var)) {
                    return $this->$var;
                } else {
                    return $this->loadRelation($var);
                }
            default:
                throw new Exception("Fonction $function invalide");
        }
    }
    
    private function loadRelation($var)
    {
        if (isset($this->foreign_keys[$var]))
        {
            return $this->load(array($this->bdd_name => $var));
        }
        
        return null;
    }
    
    private function execute($stmt)
    {
        if (!$return = $stmt->execute()) {
            throw new PDOException($stmt->errorInfo()[2]);
        }
        return $return;
    }
    
    public function truncate()
    {
        return $this->execute($this->dbh->prepare('truncate table ' . $this->bdd_name));
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
                if (($key != 'created' && $key != 'updated' && $key != 'id') || ($key == 'id' && $this->getId() != null))
                {
                    if (isset($this->$key)) {
                        $query1 .= $key . ", ";
                    }
                }
            }
            $query2 = substr($query1, 0, -2);
            $query2 .= ") VALUES (";
            foreach ($this->attributs as $key => $attribut)
            {
                if (($key != 'created' && $key != 'updated' && $key != 'id') || ($key == 'id' && $this->getId() != null)) {
                    if (isset($this->$key)) {
                        $query2 .= ':' . $key . ", ";
                    }
                }
            }
            $query = substr($query2, 0, -2);
            $query .= ")";

            $stmt  = $this->dbh->prepare($query);
            foreach ($this->attributs as $key => $attribut)
            {
                if (($key != 'created' && $key != 'updated' && $key != 'id') || ($key == 'id' && $this->getId() != null))
                {
                    if (isset($this->$key)) {
                        $stmt->bindValue(':' . $key, $this->{'get' . ucfirst($key)}());
                    }
                }
            }
            
            $this->execute($stmt);
            
            if ($this->getId() === null) {
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

            return $this->dbh->commit();
        }
        catch (PDOException $e) {
            throw new Exception($e->getMessage());
        }
    }
    
    public function setSelect($fields)
    {
        if (is_string($fields))
            $fields = array($fields);
        elseif ($fields === null)
            $fields = array();
        elseif (!is_array($fields))
            throw new ORM_Exception("Chaine ou tableau attendu");

        $this->select    = $fields;
        return $this;
    }
    
    public function getSelect()
    {
        return $this->select;
    }
    
    public function addSelect($field)
    {
        if (is_array($field))
        {
            foreach ($field as $value) {
                $this->addSelect($value);
            }
        }
        else {
            $this->select[] = $field;
        }

        return $this;
    }
    
    public function setWhere($where)
    {
        $this->where = array();
        $this->addWhere($where);

        return $this;
    }
    
    public function addWhere($where)
    {
        if ($where != null)
        {
            if (!is_array($where))
                $this->where[] = $where;
            else
                $this->where   = array_merge($this->where, $where);
        }

        return $this;
    }
    
    public function addJoin($table_name, $foreingKeysName)
    {
        $this->join[] = array($table_name, $foreingKeysName);
    }
    
    private function getSQLSelect($object = null, $foreing_keys = null)
    {
        if (empty($this->select))
        {
            $result = 'select';
            if (!isset($object)) {
                $object = $this;
            }
            $result .= $this->getSQLAttributs($object);
            return $this->getAttributsForeignKeys($object, $result, $foreing_keys);
        }
        else
        {
            $sqlSelect = 'select ';
            $sqlSelect .= implode(', ', array_filter(array_values($this->select)));

            return $sqlSelect;
        }
    }
    
    public function getSQLAttributs($object)
    {
        $result = '';
        foreach (array_flip($object->attributs) as $key) {
            $result .= ' ' . $object->bdd_name . '.' . $key . ' as ' . $object->bdd_name . '_' . $key . ',';
        }
        return $result;
    }
    
    private function getAttributsForeignKeys($object, $query, $foreing_keys, $tableName = '')
    {
        if (!isset($foreing_keys))
        {
            $query = substr($query, 0, -1);
            return $query;
        }
        
        foreach ($object->foreign_keys as $key => $datas)
        {
            if ($datas[0] != $tableName && ($this->isForeignkeys($object->bdd_name, $key, $foreing_keys)) && !is_array($datas[1]))
            {
                $foreign_name = 'ORM_' . ucfirst($datas[0]);
                $foreign_obj = new $foreign_name();
                $query .= $this->getSQLAttributs($foreign_obj);
                $query = $object->getAttributsForeignKeys($object, $query, $foreign_obj, $object->bdd_name);
            }
        }
        $query = substr($query, 0, -1);
        return $query;
    }
    
    private function isForeignkeys($table_name, $value, $foreing_keys)
    {
        foreach ($foreing_keys as $key => $data) {
            if ($data == $value && $key == $table_name) { 
                return true;
            }
        }
        
        return false;
    }
    
    private function getSQLJoin($object, $result, $foreing_keys, $tableName = '')
    {
        if (!empty($this->join))
        {
//            foreach ($this->join as $join)
//            {
//                if (isset($join['object']) && $join['object'] instanceof ORM_Query)
//                    $sql .= " {$join['type']} JOIN ({$join['object']->getSQLLoad()}) AS {$join['table']} ON {$join['on']} ";
//                else
//                    $sql .= " {$join['type']} JOIN {$join['table']} ON {$join['on']} ";
//            }
//            
//            return $sql;
        }
        else
        {
            if (!isset($foreing_keys)) {
                return $result;
            }

            foreach ($object->foreign_keys as $key => $datas)
            {
                if ($datas[0] != $tableName && ($this->isForeignkeys($object->bdd_name, $key, $foreing_keys)))
                {
                    if (is_array($datas[1]))
                    {
                        $result .= " left join " . $datas[0] . " on ";
                        foreach ($datas[1] as $keyCustom => $dataCustom)
                        {
                            $on = $object->bdd_name . "." . $keyCustom;
                            if (strpos($dataCustom, '.'))  {
                                $on = $keyCustom;
                            }
                            $onAssign = $datas[0] . "." . $dataCustom;
                            if (substr($dataCustom, 0, 1) == '(' && substr($dataCustom, -1) == ')')  {
                                $onAssign = $dataCustom;
                            }
                            $result .= $on . " = " . $onAssign . " and ";
                        }
                        $result = substr($result, 0, -5);
                    }
                    else {
                        $result .= " left join " . $datas[0] . " on " . $object->bdd_name . "." . $datas[1] . " = " . $datas[0] . "." . $datas[2];
                    }
                    $foreign_name = 'ORM_' . ucfirst($datas[0]);
                    $result = $object->getSQLJoin(new $foreign_name(), $result, $foreing_keys, $object->bdd_name);
                }
            }
            return $result;
        }
    }
    
    /**
     * RÃ©cupÃ¨re un rÃ©sulat sous forme de tableau d'objet avec ou sans enfants, peux prendre en paramÃ¨tre des clauses where sous forme de tableau ainsi qu'un order by sous forme de string
     * 
     * @param booleen $foreing_keys RÃ©cupÃ¨re ou non les objets enfants renseignÃ©s dans l'attribut foreing_keys sous forme de tableau d'objet
     * @param array $where Clause where sous forme de tableau clÃ© = valeur
     * @param string $orderby Order by sous forme de string
     * @return array(array(object BDD)) $result RÃ©sultat sous forme de teableau d'objet
     */
    function load($foreing_keys = null)
    {
        try
        {
            $this->getSQLLoad($foreing_keys);
            $stmt  = $this->dbh->prepare($this->query);
            $result = $this->fetchObjects($this->setBindValue($stmt), $foreing_keys);
        }
        catch (PDOException $e) {
            throw new Exception($e->getMessage());
        }
        return $result;
    }
    
    /**
     * Permet de rÃ©cupÃ©rer le premier rÃ©sultat de la fonction load
     */
    function loadOne($foreing_keys = null)
    {
        $result = $this->load($foreing_keys);

        if ($result)
            return $result[0];
        
        return false;
    }

    private function getSQLFrom()
    {
        return ' FROM ' . $this->bdd_name;
    }
    
    private function setBindValue($stmt)
    {
        if (!empty($this->where))
        {
            foreach ($this->where as $key => $value)
            {
                
                if (!is_int($key)) {
                    $stmt->bindValue(':' . $key, $value);
                }
            }
        }
        
        return $stmt;
    }

    private function getSQLWhere()
    {
        if (!empty($this->where))
        {
            $query = " where";
            foreach ($this->where as $key => $value)
            {
                if (is_int($key)) {
                    $query .= ' ' . $value . ' and ';
                } else {
                    $query .= ' ' . $key . ' = :' . $key . ' and ';
                }
            }
            return substr($query, 0, -5);
        }

        return '';
    }

    public function getSQLLoad($foreing_keys)
    {
        $this->query = $this->getSQLSelect($this, $foreing_keys);
        $this->query .= $this->getSQLFrom();
        $this->query = $this->getSQLJoin($this, $this->query, $foreing_keys);
        $this->query .= $this->getSQLWhere();
        $_SESSION['query'][] = $this->query;
        return $this->query;
    }

    protected function fetchObjects($stmt, $foreing_keys)
    {
        $result = array();
        if ($this->execute($stmt)) {
            while($data = $stmt->fetch())
            {
                $className = 'ORM_' . $this->bdd_name;
                $result[] = new $className($data, $foreing_keys);
            }
        }
        
        return $result;
    }
    
    public function getQuery()
    {
        return $this->query;
    }
}
