<?php

class Model
{
    protected $dbh;
    public $query;
    private $select = array();
    private $join = array();
    private $orderBy;
    private $groupBy;

    function __construct($datas = array(), $foreign_keys = false, $className = '', $limit = 200)
    {
        $this->dbh   = BDD::getConnection();
        $this->query = new Query($this);

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
        if (isset($this->foreign_keys[$var]))
        {
            $foreign_key = $this->foreign_keys[$var];
            return $this->load(array(), array($this->bdd_name => $var));
//            $query = "select * ";
//            $query .= "from " . $this->bdd_name;
//            $query = $this->foreignKeysFrom($this, $foreign_key, $query);
//            $query .= ''; // where
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
    
    public function setOrderBy($orderBy)
    {
        $this->orderBy = $orderBy;
        return $this;
    }
    
    public function getOrderBy()
    {
        return $this->orderBy;
    }
    
    public function setGroupBy($groupBy)
    {
        $this->groupBy = $groupBy;
        return $this;
    }
    
    public function addJoin($table_name, $foreingKeysName)
    {
        $this->join[] = array($table_name, $foreingKeysName);
    }
    
//    public function addLeftJoin($object, $on = null, $alias = null)
//    {
//        'performes' => array('performe','id', 'idTask'),
//        $this->join[] = array('object' => $object, 'type'   => $type, 'table'  => $table, 'on'     => $on);
//        return $this->addJoin('LEFT', $object, $on, $alias);
//    }
//    
//    public function addRightJoin($object, $on = null, $alias = null)
//    {
//        return $this->addJoin('RIGHT', $object, $on, $alias);
//    }
//    
//    public function addInnerJoin($object, $on = null, $alias = null)
//    {
//        return $this->addJoin('INNER', $object, $on, $alias);
//    }
    
//    public function getSelect($object = null, $foreing_keys = null)
//    {
//        $result = 'select';
//        if (!isset($object)) {
//            $object = $this;
//        }
//        foreach (array_flip($object->attributs) as $key) {
//            $result .= ' ' . $object->bdd_name . '.' . $key . ' as ' . $object->bdd_name . '_' . $key . ',';
//        }
//        return $this->getAttributsForeignKeys($object, $result, $foreing_keys);
//    }
    
//    private function getAttributsForeignKeys($object, $result, $foreing_keys, $tableName = '')
//    {
//        if (!isset($foreing_keys))
//        {
//            $result = substr($result, 0, -1);
//            return $result;
//        }
//        
//        foreach ($object->foreign_keys as $key => $datas)
//        {
//            if ($datas[0] != $tableName && ($this->isForeignkeys($object->bdd_name, $key, $foreing_keys)) && !is_array($datas[1]))
//            {
//                $foreign_name = 'ORM_' . ucfirst($datas[0]);
//                $foreign_obj = new $foreign_name();
//                $result .= $this->getAttributs($foreign_obj);
//                $result = $object->getAttributsForeignKeys($foreign_obj, $result, $object->bdd_name);
//            }
//        }
//        $result = substr($result, 0, -1);
//        return $result;
//    }
//    
//    private function isForeignkeys($table_name, $value, $foreing_keys)
//    {
//        foreach ($foreing_keys as $key => $data) {
//            if ($data == $value && $key == $table_name) { 
//                return true;
//            }
//        }
//        
//        return false;
//    }
    
//    private function addJoin($object, $result, $foreing_keys, $tableName = '')
//    {
//        if (!isset($foreing_keys)) {
//            return $result;
//        }
//        
//        foreach ($object->foreign_keys as $key => $datas)
//        {
//            if ($datas[0] != $tableName && ($this->isForeignkeys($object->bdd_name, $key, $foreing_keys)))
//            {
//                if (is_array($datas[1]))
//                {
//                    $result .= " left join " . $datas[0] . " on ";
//                    foreach ($datas[1] as $keyCustom => $dataCustom)
//                    {
//                        $on = $object->bdd_name . "." . $keyCustom;
//                        if (strpos($dataCustom, '.'))  {
//                            $on = $keyCustom;
//                        }
//                        $onAssign = $datas[0] . "." . $dataCustom;
//                        if (substr($dataCustom, 0, 1) == '(' && substr($dataCustom, -1) == ')')  {
//                            $onAssign = $dataCustom;
//                        }
//                        $result .= $on . " = " . $onAssign . " and ";
//                    }
//                    $result = substr($result, 0, -5);
//                }
//                else {
//                    $result .= " left join " . $datas[0] . " on " . $object->bdd_name . "." . $datas[1] . " = " . $datas[0] . "." . $datas[2];
//                }
//                $foreign_name = 'ORM_' . ucfirst($datas[0]);
//                $result = $object->getJoin(new $foreign_name(), $result, $foreing_keys, $object->bdd_name);
//            }
//        }
//        return $result;
//    }
    
    /**
     * RÃ©cupÃ¨re un rÃ©sulat sous forme de tableau d'objet avec ou sans enfants, peux prendre en paramÃ¨tre des clauses where sous forme de tableau ainsi qu'un order by sous forme de string
     * 
     * @param booleen $foreing_keys RÃ©cupÃ¨re ou non les objets enfants renseignÃ©s dans l'attribut foreing_keys sous forme de tableau d'objet
     * @param array $where Clause where sous forme de tableau clÃ© = valeur
     * @param string $orderby Order by sous forme de string
     * @return array(array(object BDD)) $result RÃ©sultat sous forme de teableau d'objet
     */
    function load($where = array(), $foreing_keys = null, $orderby = '')
    {
        try
        {
            $result = array();
            $query = new Query($this, $foreing_keys);
            $query = $this->getSelect($this, $foreing_keys) . " from " . $this->bdd_name . $this->getJoin($this, $query, $foreing_keys);
            
            $nbWhere = count($where);
            if ($nbWhere > 0)
            {
                $query .= " where";
                foreach ($where as $key => $value) {
                    $query .= ' ' . $key . ' = :' . $key . ' and ';
                }
                $query = substr($query, 0, -5);
                
                $stmt  = $this->dbh->prepare($query);
                
                foreach ($where as $key => $value) {
                    $stmt->bindValue(':' . $key, $value);
                }
            }
            else {
                $stmt  = $this->dbh->prepare($query);
            }
            
            if ($this->execute($stmt)) {
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
    
    private function foreignKeysFrom($object, $datas, $query)
    {
        if (is_array($datas[1]))
        {
            $query .= " left join " . $datas[0] . " on ";
            foreach ($datas[1] as $key => $dataCustom)
            {
                $on = $object->bdd_name . "." . $key;
                if (strpos($dataCustom, '.'))  {
                    $on = $key;
                }
                $onAssign = $datas[0] . "." . $dataCustom;
                if (substr($dataCustom, 0, 1) == '(' && substr($dataCustom, -1) == ')')  {
                    $onAssign = $dataCustom;
                }
                $query .= $on . " = " . $onAssign . " and ";
            }
            $query = substr($query, 0, -5);
        }
        else {
            $query .= " left join " . $datas[0] . " on " . $object->bdd_name . "." . $datas[1] . " = " . $datas[0] . "." . $datas[2];
        }
        return $query;
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

    /**
     * Ajout de champs � la s�lection.
     * Les champs sont les noms de colonnes en base de donn�es.
     * 
     * @param string/array $fields Cha�ne de s�lection ou tableau de champs
     * @return ORM_Query
     */

    /**
     * Pr�cision du nombre d'enregistrement que la requ�te doit s�lectionner.
     * 
     * @param integer $top Nombre d'enregistrements
     * @return ORM_Query
     * @throws ORM_Exception
     */
//    public function setTop($top)
//    {
//        if (!is_integer($top))
//            throw new ORM_Exception("Entier attendu");
//        $this->top = $top;
//        return $this;
//    }

    /**
     * Param�trage de la s�lection. Si non pr�cis�, tous les champs sont s�lectionn�s.
     * Les champs sont les noms des attributs des objets d'ORM.
     * 
     * @param array $fields
     * @return ORM_Query
     */
//    public function setSelectAttributes(array $fields)
//    {
//        $attributes = array_flip($this->ormObject->getAttributeNames());
//
//        foreach ($fields as $field)
//        {
//            if (isset($attributes[$field]))
//                $this->select[$field] = $attributes[$field];
//        }
//
//        return $this;
//    }

    /**
     * Ajout d'une jointure � gauche
     * 
     * @uses ORM_Abstract::addJoin()
     * @param string/ORM_Abstract/ORM_Query $object Entit� � adjoindre
     * @param string $on Clause ON
     * @param string $alias Alias de la jointure
     * @return ORM_Query
     */

    /**
     * Ajout d'une jointure � droite
     * 
     * @uses ORM_Abstract::addJoin()
     * @param string/ORM_Abstract/ORM_Query $object Entit� � adjoindre
     * @param string $on Clause ON
     * @param string $alias Alias de la jointure
     * @return ORM_Query
     */

    /**
     * Ajout d'une jointure interne
     * 
     * @uses ORM_Abstract::addJoin()
     * @param string/ORM_Abstract/ORM_Query $object Entit� � adjoindre
     * @param string $on Clause ON
     * @param string $alias Alias de la jointure
     * @return ORM_Query
     */

    /**
     * Ajout d'une jointure � la requ�te.
     * Si l'entit� adjointe est :
     * 
     * - une cha�ne
     * Si cette cha�ne est un nom d'objet d'ORM existant, la table correspondant � cet objet sera utilis�e en tant que jointure.
     * Si la cha�ne se termine par un '|' suivi du nom d'une relation, cette relation sera utilis�e en clause ON.
     * (dans ce cas, les clauses ON suppl�mentaires seront tout de m�me prises en compte)
     * 
     * Exemple :
     * @code
     * $query = $police->getQuery()
     *         ->addLeftJoin('Data_Filiale|polices', "(POL_DATEFIN IS NULL OR POL_DATEFIN >= CONVERT(DATETIME, '".date('Y-m-d')."',120))")
     *         ->addLeftJoin('Data_Garantie|police')
     *         ->setSelect(array("CASE POL_LIB_RISQUE WHEN 'Sant�' THEN 1 ELSE 2 END AS IS_SANTE", 'POL_DATDEB'))
     *         ->setTop(10)
     *         ->setDistinct(true)
     *         ->setOrderBy("IS_SANTE, POL_DATDEB DESC");
     * @endcode
     * 
     * - un objet d'ORM
     * La table correspondant � cet objet sera utilis�e en tant que jointure.
     * 
     * - un objet ORM_Query
     * La jointure sera la sous-requ�te correspondant � cet objet. Dans ce cas, un alias est obligatoire, et la clause ON ne pourra �tre aliment�e automatiquement.
     * 
     * Exemple :
     * @code
     *  $query = clone($queryPolices);
     *  $query->setSelect(array('POL_NUMPOL', 'POL_ASSURE', 'POL_NUM_POLICE', 'POL_NUM_DOSSIER', 'POL_CODETAT'));
     *  $objects = ORM_Factory::get('Data_Garantie')->query()
     *                  ->addSelect(array('POL_NUMPOL', 'POL_NUM_POLICE', 'POL_NUM_DOSSIER', 'POL_ASSURE', 'POL_CODETAT'))
     *                  ->addInnerJoin($query, 'GAD_POLICE = POL_NUMPOL', 'polices')
     *                  ->setOrderBy('POL_ASSURE, POL_NUM_POLICE, POL_NUM_DOSSIER, GAD_LIMGAR_LIBE, GAD_LIBELLE')
     *                  ->loadCollection();
     * @endcode
     * 
     * @example "ORM : requ�tes et jointures via ORM_Query"
     * @param string $type Cha�ne parmis les valeurs 'LEFT', 'RIGHT' ou 'INNER'
     * @param string/ORM_Abstract/ORM_Query $object Entit� � adjoindre
     * @param string $on Clause ON
     * @param string $alias Alias de la jointure
     * @return ORM_Query
     * @throws ORM_Exception
     */

    /**
     * Param�trage de la clause WHERE.
     * Remplace toutes les clauses WHERE existantes.
     * 
     * @see ORM_Query::getSQLWhere() Diff�rentes mani�res de fournir les clauses WHERE
     * @param string/array/ORM_Query $where
     * @return ORM_Query
     */

    /**
     * Ajout d'une clause WHERE.
     * 
     * @see ORM_Query::getSQLWhere() Diff�rentes mani�res de fournir les clauses WHERE
     * @param string/array/ORM_Query $where
     * @return ORM_Query
     */

    /**
     * Ajoute une clause UNION � la requ�te.
     *
     * Le premier param�tre $select peut �tre un string, un objet ORM_Query
     * existant ou un array avec l'un de ces types.
     *
     * @param  array|string|ORM_Query $select Une ou plusieurs clauses pour le UNION.
     * @return ORM_Query
     */
//    public function addUnion($select = array(), $type = self::SQL_UNION)
//    {
//        if (!is_array($select)) {
//            $select = array($select);
//        }
//
//        if (!in_array($type, self::$_unionTypes)) {
//            throw new ORM_Exception("Invalid union type '{$type}'");
//        }
//
//        foreach ($select as $target) {
//            $this->union[self::UNION][] = array($target, $type);
//        }
//
//        return $this;
//    }

    /**
     * Param�trage de la clause de tri.
     * 
     * @param string $orderBy
     * @return ORM_Query
     */

    /**
     * Param�trage du groupBy.
     * 
     * @param string $orderBy
     * @return ORM_Query
     */

    /**
     * Renvoie la clause de tri.
     * 
     * @return string Clause de tri
     */

    /**
     * Renvoie du nombre d'enregistrement que la requ�te doit s�lectionner.
     * 
     * @return integer Nombre d'enregistrements
     */
//    public function getTop()
//    {
//        return $this->top;
//    }

    /**
     * Param�trage du nombre d'enregistrement � renvoyer au parcours du jeu de r�sultats
     * 
     * @param integer $count Nombre d'enregistrements
     * @return ORM_Query
     */
//    public function setCount($count)
//    {
//        $this->count = $count;
//        return $this;
//    }

    /**
     * Param�trage du d�calage sur le jeu de r�sultats
     * 
     * @param integer $count Nombre d'enregistrements dont le curseur doit se d�caler
     * @return ORM_Query
     */
//    public function setOffset($offset)
//    {
//        $this->offset = $offset;
//        return $this;
//    }

    /**
     * Renvoie la liste des champs s�lectionn�s
     * 
     * @return array Tableau de nom des champs en base de donn�es
     */
    
    /**
     * Renvoie le code SQL correspondant � la clause SELECT.
     * G�re les filtres �ventuels d�clar�s au sein des objets d'ORM.
     * Les filtres ne sont pas g�r�s si votre champ est contenu dans une chaine de caract�res
     * 
     * @return string Cha�ne SQL
     */
    protected function getSQLSelect()
    {
        $fields = array();

        if ($this->select === null && $this->ormObject !== null && $this->ormObject instanceof ORM_Abstract)
            $fields = array_keys($this->ormObject->getAttributeNames());
        elseif ($this->select !== null)
            $fields = array_values($this->select);
            
        $fields = array_filter($fields);
        $fields = array_merge($fields, $this->addSelect);

        if ($this->ormObject instanceof ORM_Abstract)
        {
            $filters = array();

            // R�cup�ration filtres de l'objet d'ORM
            $ormObjectFilters                     = $this->ormObject->getFilters();
            if (is_array($ormObjectFilters) && !empty($ormObjectFilters))
                $filters[get_class($this->ormObject)] = $ormObjectFilters;

            // R�cup�ration filtres des jointures
            foreach ($this->join as $join)
            {
                if (isset($join['object']) && $join['object'] instanceof ORM_Abstract)
                {
                    $joinFilters                         = $join['object']->getFilters();
                    if (is_array($joinFilters) && !empty($joinFilters))
                        $filters[get_class($join['object'])] = $joinFilters;
                }
            }

            if (!empty($filters))
            {
                foreach ($filters as $object => $list)
                {
                    // Application des filtres
                    foreach ($list as $field => $filter)
                    {
                        $index = array_search($field, $fields);
                        if ($index !== false)
                        {
                            if (strtolower($filter) == 'text')
                                $this->distinct = false;

                            if (method_exists($object, 'filter' . ucfirst($filter)))
                            {
                                eval('$newValue = ' . $object . '::filter' . $filter . '($field);');
                                $fields[$index] = $newValue;
                            }
                        }
                    }
                }
            }
        }

        if (empty($fields))
            $fields[] = '*';

        $sqlSelect = 'SELECT ' . (($this->distinct == true) ? 'DISTINCT ' : '') . (($this->top !== null) ? "TOP $this->top " : '');
        $sqlSelect .= implode(', ', $fields);

        return $sqlSelect;
    }

    /**
     * Renvoie le code SQL correspondant aux jointures.
     * Les jointures peuvent �tre faites sur des tables ou des sous-requ�tes.
     * 
     * @return string Cha�ne SQL
     */
    protected function getSQLJoin()
    {
        $sql = '';

        if (!empty($this->join))
        {
            foreach ($this->join as $join)
            {
                if (isset($join['object']) && $join['object'] instanceof ORM_Query)
                    $sql .= " {$join['type']} JOIN ({$join['object']->getSQLLoad()}) AS {$join['table']} ON {$join['on']} ";
                else
                    $sql .= " {$join['type']} JOIN {$join['table']} ON {$join['on']} ";
            }
        }

        return $sql;
    }

    /**
     * Activation ou d�sactivation de la s�lection distincte (activ�e par d�faut).
     * 
     * @param boolean $flag Si � true, s�lection distincte
     * @return ORM_Query
     */
    public function setDistinct($flag = true)
    {
        $this->distinct = $flag;
        return $this;
    }

    /**
     * Renvoie le code SQL correspondant au FROM.
     * Le FROM peut �tre une table ou une sous-requ�te.
     * 
     * @return string Cha�ne SQL
     */
    protected function getSQLFrom()
    {
        if ($this->ormObject instanceof ORM_Abstract)
            $sqlFrom = " FROM {$this->ormObject->getTableName()} ";
        elseif ($this->ormObject instanceof ORM_Query)
            $sqlFrom = " FROM (" . $this->ormObject->getSQLLoad() . ") ";

        if ($this->fromAs != null)
            $sqlFrom .= " AS $this->fromAs ";
        return $sqlFrom;
    }

    /**
     * Renvoie le code SQL correspondant � la clause WHERE.
     * 
     * @return string Cha�ne SQL
     */
    public function getSQLWhere($avecWhere = true)
    {
        $sqlWhere = '';

        if (!empty($this->where))
        {
            $whereParts = array();
            foreach ($this->where as $field_name => $field_value)
            {
                if ($field_value instanceof ORM_Query)
                {
                    $field_value->setOrderBy(null);
                    $whereParts[] = $field_name . ' IN (' . $field_value->getSQLLoad() . ')';
                }
                elseif (is_numeric($field_name))
                    $whereParts[] = $field_value;
                elseif (is_string($field_name)){
                    if($field_value === null || $field_value == ""){// si $field_value est une string contenant 'null' nous ne passons pas ici
                        $whereParts[] = '('.$field_name . ' IS NULL OR ' . $field_name . " = '' )";
                    }
                    $whereParts[] = $field_name . ' = ' . self::quote($field_value);
                }

            }

            $sqlWhere = implode(' AND ', $whereParts);
        }

        if ($sqlWhere && $avecWhere)
            $sqlWhere = ' WHERE ' . $sqlWhere;

        return $sqlWhere;
    }

    /**
     * Renvoie le code SQL correspondant � la clause ORDER BY.
     *
     * @return string Cha�ne SQL
     */
    protected function getSQLOrderBy()
    {
        $sqlOrderBy = '';

        if ($this->orderBy != null)
        {
            $sqlOrderBy = ' ORDER BY ' . $this->orderBy;

            if (strstr(strtolower($sqlOrderBy), 'select') == true)
                $this->distinct = false;
            elseif (strstr(strtolower($sqlOrderBy), 'isnull') == true)
                $this->distinct = false;
        }

        return $sqlOrderBy;
    }

    /**
     * Renvoie le code SQL correspondant � la clause UNION.
     *
     * @return string Cha�ne SQL
     */
    protected function getSQLUnion()
    {
        $sqlUnion = '';

        if ($this->union != null)
        {
            $unions = count($this->union[self::UNION]);
            foreach ($this->union[self::UNION] as $union) {
                list($target, $type) = $union;
                if ($target instanceof ORM_Query) {
                    $target = $target->getSQLLoad();
                }
                $sqlUnion .= ' ' . $type . ' ' . $target;
            }
        }

        return $sqlUnion;
    }

    /**
     * Renvoie le code SQL correspondant � la clause GROUP BY.
     * 
     * @return string Cha�ne SQL
     */
    protected function getSQLGroupBy()
    {
        $sqlGroupBy = '';

        if ($this->groupBy != null)
        {
            $sqlGroupBy = ' GROUP BY ' . $this->groupBy;
            $this->distinct = false;
        }

        return $sqlGroupBy;
    }

    /**
     * Renvoie le code SQL correspondant � une s�lection.
     * 
     * @return string Cha�ne SQL
     */
    public function getSQLLoad()
    {
        if ($this->distinct === null)
            $this->distinct = true;

        $sql = $this->getSQLSelect() . ' ' . $this->getSQLFrom() . ' ' . $this->getSQLJoin() . ' ' . $this->getSQLWhere() . ' ' . $this->getSQLUnion() . ' ' . $this->getSQLGroupBy(). ' ' .$this->getSQLOrderBy();
        return $sql;
    }

    /**
     * Renvoie le code SQL correspondant � une suppression.
     * 
     * @return string Cha�ne SQL
     */
    public function getSQLDelete()
    {
        $sql = 'DELETE ' . $this->getSQLFrom() . ' ' . $this->getSQLWhere();
        return $sql;
    }

    /**
     * Renvoie un tableau d'objets d'ORM depuis un jeu de r�sultats
     * 
     * @param resource $resultset Jeu de r�sultats
     * @return array Tableau d'objets d'ORM
     */
    
    protected function fetchObjects($resultset, $cascade)
    {
        $className = get_class($this->ormObject);
        $instances = array();
        
        // Parcours le resultset
        while ($tuple = BDD::getConnection()->fetchArray($resultset))
        {
            $instance    = new $className($tuple, $cascade);
            $instances[] = $instance;
        }

        return $instances;
    }

    /**
     * Renvoie un tableau de tableaux depuis un jeu de r�sultats
     * 
     * @param resource $resultset Jeu de r�sultats
     * @return array Tableau de paires nom de champ en base => valeur
     */
    protected function fetchArray($resultset)
    {
        $instances = array();

        // Parcours le resultset
        while ($tuple = BDD::getConnection()->fetchArray($resultset)) {
            $instances[] = $tuple;
        }

        return $instances;
    }
    
    /**
     * Renvoie un tableau de paires cl� => valeur depuis le jeu de r�sultats
     * - Si la requ�te ram�ne un champ, la cl� est num�rique est la valeur est le contenu du champ
     * - Si la requ�te ram�ne deux champs, la cl� est la valeur du premier champ, la valeur celle du second champ
     * 
     * @param resource $resultset Jeu de r�sultats
     * @return array Tableau de paires nom de champ en base => valeur
     */
    protected function fetchList($resultset)
    {
        $instances = array();

        // Parcours le resultset
        while ($tuple = BDD::getConnection()->fetchRow($resultset))
        {
            $countTuple = count($tuple);
            if ($countTuple == 1)
                $instances[] = $tuple[0];
            elseif ($countTuple == 2)
                $instances[$tuple[0]] = $tuple[1];
            else
                throw new ORM_Exception("Pour constituer une liste, la requ�te doit ramener 1 ou 2 champs");
        }

        return $instances;
    }

    /**
     * Renvoie le nombre de r�sultats correspondant � la s�lection
     * 
     * @return integer Nombre de r�sultats
     * @throws ORM_Exception
     */
    public function count()
    {
        try
        {
            $sql       = $this->getSQLLoad();
            $resultset = BDD::getConnection()->execute($sql);
            return BDD::getConnection()->count($resultset);
        }
        catch (BDD_Exception $e)
        {
            throw new ORM_Exception('Probl�me lors du chargement d\'objet(s) ' . get_class($this->ormObject), $e);
        }
    }

    /**
     * Chargement du premier objet d'ORM renvoy� par la requ�te param�tr�e
     * 
     * @return ORM_Abstract Objet d'ORM ou null
     * @throws ORM_Exception
     */
    
//    public function loadOne($cascade = false)
//    {
//        try
//        {
//            if ($this->offset == null)
//                $this->setTop(1);
//
//            $this->setCount(1);
//            $result = $this->load($cascade);
//
//            if (count($result) == 1)
//                return $result[0];
//            else
//                return null;
//        }
//        catch (BDD_Exception $e)
//        {
//            throw new ORM_Exception('Probl�me lors du chargement d\'objet(s) ' . get_class($this->ormObject), $e);
//        }
//    }
    
    /**
     * Chargement du tableau d'objets d'ORM renvoy�s par la requ�te param�tr�e
     * 
     * @param array $cascade
     * @return array Tableau d'objets d'ORM
     * @throws ORM_Exception
     */
//    public function load($cascade = false)
//    {
//        try
//        {
//            $sql = $this->getSQLLoad();
//
//            if (self::$cacheEnabled && isset(self::$cacheSQL['objects'][$sql]) && is_array(self::$cacheSQL['objects'][$sql]))
//            {
//                BDD::getConnection()->getLogObject()->info("[cache] " . $sql);
//                return self::$cacheSQL['objects'][$sql];
//            }
//            else
//            {
//                // Pagination
//                if ($this->offset !== null && $this->count !== null)
//                {
//                    if (empty($this->getSQLOrderBy()))
//                        throw new ORM_Exception("La clause ORDER BY est obligatoire lors d'une pagination");
//
//                    $sql .= ' OFFSET ' . $this->offset . ' ROWS FETCH NEXT ' . $this->count . ' ROWS ONLY';
//                }
//                
//                $resultset = BDD::getConnection()->execute($sql);
//                $instances = $this->fetchObjects($resultset, $cascade);
//                
//                if (self::$cacheEnabled) {
//                    self::$cacheSQL['objects'][$sql] = $instances;
//                }
//
//                return $instances;
//            }
//        }
//        catch (BDD_Exception $e) {
//            throw new ORM_Exception('Probl�me lors du chargement d\'objet(s) ' . get_class($this->ormObject), $e);
//        }
//    }

    /**
     * Chargement du tableau de tableaux renvoy�s par la requ�te param�tr�e
     * 
     * @return array Tableau de tableaux de paires nom du champ en base de donn�es => valeur
     * @throws ORM_Exception
     */
    public function loadArray()
    {
        try
        {
            $sql = $this->getSQLLoad();

            if (self::$cacheEnabled && isset(self::$cacheSQL['array'][$sql]) && is_array(self::$cacheSQL['array'][$sql]))
            {
                BDD::getConnection()->getLogObject()->info("[cache] " . $sql);
                return self::$cacheSQL['array'][$sql];
            }
            else
            {
                // Pagination
                if ($this->offset !== null && $this->count !== null)
                {
                    if (empty($this->getSQLOrderBy()))
                        throw new ORM_Exception("La clause ORDER BY est obligatoire lors d'une pagination");

                    $sql .= ' OFFSET ' . $this->offset . ' ROWS FETCH NEXT ' . $this->count . ' ROWS ONLY';
                }
                
                $resultset = BDD::getConnection()->execute($sql);
                $instances = $this->fetchArray($resultset);
                
                if (self::$cacheEnabled)
                    self::$cacheSQL['array'][$sql] = $instances;

                return $instances;
            }
        }
        catch (BDD_Exception $e)
        {
            throw new ORM_Exception('Probl�me lors du chargement d\'objet(s) ' . get_class($this->ormObject), $e);
        }
    }

    /**
     * Chargement d'une collection d'objets d'ORM renvoy�s par la requ�te param�tr�e
     * 
     * @return ORM_Collection Collection d'objets d'ORM
     * @throws ORM_Exception
     */
    public function loadCollection($cascade = false)
    {
        try
        {
            $sql = $this->getSQLLoad();

            if (self::$cacheEnabled && isset(self::$cacheSQL['collection'][$sql]) && self::$cacheSQL['collection'][$sql] instanceof ORM_Collection)
            {
                BDD::getConnection()->getLogObject()->info("[cache] " . $sql);
                return self::$cacheSQL['collection'][$sql];
            }
            else
            {
                //si $maxRow a �t� renseign� par une m�thode count personnalis�e (voir "load()" de ORM_Pager) alors on utilise la methode optimis� pour du pager, pour uniquement ramener les "nbRowParPage" enregistrement
                if($this->offset !== null && $this->count !== null && $this->maxRow !== null)
                {
                    if (empty($this->getSQLOrderBy()))
                        throw new ORM_Exception("La clause ORDER BY est obligatoire lors d'une pagination");

                    $sql .= ' OFFSET ' . $this->offset . ' ROWS FETCH NEXT ' . $this->count . ' ROWS ONLY';
                    
//                    $select = $this->getSQLSelect() ." ,ROW_NUMBER() over (ORDER BY ". $this->getOrderBy() .") AS RowNum ";
//                    $sql =  $select . $this->getSQLFrom() . " " .$this->getSQLJoin(). " " . $this->getSQLWhere(). " " . $this->getSQLGroupBy();
//                    $sql = ";WITH results_cte as (". $sql ." ) select * from results_cte where RowNum >= ". ($this->offset+1)  ." AND RowNum < ". ($this->offset + $this->count+1 );
                    $resultset  = BDD::getConnection()->execute($sql);
                    $collection = new ORM_Collection($resultset, $this->ormObject, $this->offset, $this->count,$this->maxRow, $cascade);
                }
                else
                {
                    $resultset  = BDD::getConnection()->execute($sql);
                    $collection = new ORM_Collection($resultset, $this->ormObject, $this->offset, $this->count, null, $cascade);
                }
                if (self::$cacheEnabled)
                    self::$cacheSQL['collection'][$sql] = $collection;
                return $collection;
            }
        }
        catch (BDD_Exception $e)
        {
            throw new ORM_Exception('Probl�me lors du chargement d\'objet(s) ' . get_class($this->ormObject), $e);
        }
    }
    
    /**
     * Chargement d'une collection d'objets d'ORM renvoy�s par la proc�dure
     * 
     * Exemple d'appel de procedure stock�e :
     * 
     * Avec le pager :
     * $queryPager = ORM_Factory::get('Site_DemandeRemboursement')->query();
     * $testPager = $queryPager->getPager();
     * $resultPager = $testPager->loadProcedure('msh_site.dbo.getDemandes', array(array('name' => '@numAdherent', 'value' => 'TESTPREVINTER', 'type' => 'varchar')), null, null, "countNbDemandeRemboursement", array('NUM_ADHERENT'=> 'TESTPREVINTER'));
     * 
     * Sans pager :
     * $test = ORM_Factory::get('Site_DemandeRemboursement')->loadProcedure('msh_site.dbo.getDemandes', array(array('name' => '@numAdherent', 'value' => 'TESTPREVINTER', 'type' => 'varchar')));
     * 
     * @return ORM_Collection Collection d'objets d'ORM
     * @throws ORM_Exception
     */
    public function loadProcedure($name, $binds = array(), $type = 'array')
    {
        try
        {
            $sql = $name;
            if($this->offset !== null && $this->count !== null && $this->maxRow !== null)
                $sql .= $this->offset.$this->count.$this->maxRow;

            if (self::$cacheEnabled && isset(self::$cacheSQL['procedure'][$sql]) && self::$cacheSQL['procedure'][$sql] instanceof ORM_Collection)
            {
                BDD::getConnection()->getLogObject()->info("[cache] " . $sql);
                return self::$cacheSQL['collection'][$sql];
            }
            else
            {
                $conn = BDD::getConnection();
            
                if($this->offset !== null && $this->count !== null && $this->maxRow !== null)
                {
//                    $proc = $conn->initProcedure($name, $binds, $this->offset, $this->count);
                    $resultset = $conn->executeProcedure($name, $binds);
                    $instances = new ORM_Collection($resultset, $this->ormObject, $this->offset, $this->count,$this->maxRow);
//                        $instances = $this->fetchArray($resultset);
                }
                else
                {
                    $resultset = $conn->executeProcedure($name, $binds);
                    if ($type == 'array') {
                        $instances = $this->fetchArray($resultset);
                    } else {
                        $instances = new ORM_Collection($resultset, $this->ormObject, $this->offset, $this->count);
                    }
                }
                if (self::$cacheEnabled)
                    self::$cacheSQL['collection'][$sql] = $instances;

//                $conn->freeProcedure($proc);
                
                return $instances;
            }
        }
        catch (BDD_Exception $e)
        {
            throw new ORM_Exception('Probl�me lors du chargement d\'objet(s) ' . get_class($this->ormObject), $e);
        }
    }

    /**
     * Nettoyage du cache des requ�tes SQL
     */
    public static function clearCache()
    {
        if (self::$cacheEnabled)
            self::$cacheSQL = array();
    }

    /**
     * Suppression des objets concern�s par la requ�te param�tr�e
     * @return null
     */
//    public function delete()
//    {
//        self::clearCache();
//
//        $query = $this->getSQLDelete();
//        return BDD::getConnection()->execute($query);
//    }

    /**
     * Echappement d'une cha�ne pour pr�paration � la requ�te SQL : doublage des quotes.
     * - permet de passer des quotes dans les valeurs
     * - prot�ge contre l'injection SQL
     * 
     * @param string $string Cha�ne � �chapper
     * @return string
     */
    public static function escape($string)
    {
        return str_replace(self::QUOTE_CHAR, self::QUOTE_CHAR_ESCAPED, $string);
    }

    /**
     * Mise d'une cha�ne entre quote pour pr�paration � la requ�te SQL : 
     * - remplacement par NULL si la valeur est nulle
     * - mise entre quotes
     * - �chappement
     * - permet de passer des quotes dans les valeurs
     * - prot�ge contre l'injection SQL
     * 
     * @see ORM_Query::escape()
     * @param string $string Cha�ne � mettre entre quotes
     * @return string
     */
    public static function quote($value)
    {
        switch (true)
        {
            case $value === null:
                return 'NULL';

            default:
                return self::QUOTE_CHAR . self::escape($value) . self::QUOTE_CHAR;
        }
    }

    /**
     * Renvoie un nouveau pager, initialis� sur cette requ�te.
     * 
     * Exemple :
     * @code
     * $query                 = ORM_Factory::get('Data_Decompte')->addWhere(array('SIN_TYPE' => 1));
     * $pager                 = $query->getPager();
     * $decomptes             = $pager->load();
     * $this->view->pager     = $pager;
     * @endcode
     * 
     * @example "ORM : instanciation d'un pager via ORM_Query"
     * @return ORM_Pager
     */
    public function getPager()
    {
        return new ORM_Pager($this);
    }

    public function getOrmObject()
    {
        return $this->ormObject;
    }
    public function setMaxRow($nbRow)
    {
        $this->maxRow = $nbRow;
    }
    public function getMaxRow()
    {
        return $this->maxRow;
    }
    
}
