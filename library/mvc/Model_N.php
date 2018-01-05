<?php

/**
 * @author nid
 * @category   ORM
 * @package    ORM_Query
 * 
 * Objet permettant de composer une requête de sélection/suppression SQL.
 */
class ORM_Query
{

    const QUOTE_CHAR         = '\'';
    const QUOTE_CHAR_ESCAPED = '\'\'';
    const UNION              = 'union';
    const SQL_UNION          = 'UNION';
    const SQL_UNION_ALL      = 'UNION ALL';

    private static $cacheSQL = array();
    private static $cacheEnabled = null;
    protected $ormObject;
    protected $select       = null;
    protected $addSelect    = array();
    protected $top    = null;
    protected $fromAs = null;
    protected $where  = array();
    protected $orderBy  = null;
    protected $groupBy  = null;
    protected $union    = array();
    protected $distinct = null;
    protected $count    = null;
    protected $offset   = null;
    protected $maxRow   = null;
    protected $join     = array();

    /**
     * Spécification des types d'UNION légals.
     *
     * @var array
     */
    protected static $_unionTypes = array(
        self::SQL_UNION,
        self::SQL_UNION_ALL
    );

    /**
     * Création d'un nouvel objet de requête, se basant :
     * - sur un objet étendant ORM_Abstract. Dans ce cas, la table de base est celle de l'objet d'ORM
     * - sur un objet ORM_Query. Dans ce cas, la table est une sous-requête, et l'alias est obligatoire
     * 
     * Par défaut, la sélection de tous les champs en DISTINCT est effectuée, sauf si un filtre "DISTINCT" est à false sur l'objet d'ORM.
     * 
     * @see ORM_Query::setDistinct()
     * @param ORM_Abstract/ORM_Query $object Objet sur lequel est basée la requête 
     * @param string $as Alias de la table
     * @throws ORM_Exception
     */
    public function __construct($object, $as = null)
    {
        if (!isset(self::$cacheEnabled))
        {
            if (isset(NCore_Doctrine_Config::getInstance()->bdd->cache->enable))
                self::$cacheEnabled = NCore_Doctrine_Config::getInstance()->bdd->cache->enable;
            else
                self::$cacheEnabled = false;
        }

        if ($object instanceof ORM_Abstract)
        {
            $this->ormObject = $object;

            $filters        = $this->ormObject->getFilters();
            if (is_array($filters) && array_key_exists('distinct', $filters) && $filters['distinct'] == false)
                $this->distinct = false;
        }
        elseif ($object instanceof ORM_Query)
        {
            if ($object->getOrderBy() !== null && $object->getTop() === null)
                $object->setOrderBy(null);

            $this->ormObject = $object;

            if ($as == null)
                throw new ORM_Exception("L'alias 'as' n'est pas renseigné dans le cas d'une sous-requête");
        }

        if (is_string($as))
            $this->fromAs = $as;
    }

    /**
     * Paramétrage de la sélection. Si non précisé, tous les champs sont sélectionnés.
     * Remplace tous les champs de sélection existants.
     * Les champs sont les noms de colonnes en base de données.
     * Les filtres ne sont pas gérés si votre champ est contenu dans une chaine de caractères
     * 
     * @param string/array $fields Chaîne de sélection ou tableau de champs
     * @return ORM_Query
     * @throws ORM_Exception
     */
    public function setSelect($fields)
    {
        if (is_string($fields))
            $fields = array($fields);
        elseif ($fields === null)
            $fields = array();
        elseif (!is_array($fields))
            throw new ORM_Exception("Chaine ou tableau attendu");

        $this->select    = $fields;
        $this->addSelect = array();
        return $this;
    }

    /**
     * Ajout de champs à la sélection.
     * Les champs sont les noms de colonnes en base de données.
     * 
     * @param string/array $fields Chaîne de sélection ou tableau de champs
     * @return ORM_Query
     */
    public function addSelect($field)
    {
        if (is_array($field))
        {
            foreach ($field as $value)
                $this->addSelect($value);
        }
        else
            $this->addSelect[] = $field;

        return $this;
    }

    /**
     * Précision du nombre d'enregistrement que la requête doit sélectionner.
     * 
     * @param integer $top Nombre d'enregistrements
     * @return ORM_Query
     * @throws ORM_Exception
     */
    public function setTop($top)
    {
        if (!is_integer($top))
            throw new ORM_Exception("Entier attendu");
        $this->top = $top;
        return $this;
    }

    /**
     * Paramétrage de la sélection. Si non précisé, tous les champs sont sélectionnés.
     * Les champs sont les noms des attributs des objets d'ORM.
     * 
     * @param array $fields
     * @return ORM_Query
     */
    public function setSelectAttributes(array $fields)
    {
        $attributes = array_flip($this->ormObject->getAttributeNames());

        foreach ($fields as $field)
        {
            if (isset($attributes[$field]))
                $this->select[$field] = $attributes[$field];
        }

        return $this;
    }

    /**
     * Ajout d'une jointure à gauche
     * 
     * @uses ORM_Abstract::addJoin()
     * @param string/ORM_Abstract/ORM_Query $object Entité à adjoindre
     * @param string $on Clause ON
     * @param string $alias Alias de la jointure
     * @return ORM_Query
     */
    public function addLeftJoin($object, $on = null, $alias = null)
    {
        return $this->addJoin('LEFT', $object, $on, $alias);
    }

    /**
     * Ajout d'une jointure à droite
     * 
     * @uses ORM_Abstract::addJoin()
     * @param string/ORM_Abstract/ORM_Query $object Entité à adjoindre
     * @param string $on Clause ON
     * @param string $alias Alias de la jointure
     * @return ORM_Query
     */
    public function addRightJoin($object, $on = null, $alias = null)
    {
        return $this->addJoin('RIGHT', $object, $on, $alias);
    }

    /**
     * Ajout d'une jointure interne
     * 
     * @uses ORM_Abstract::addJoin()
     * @param string/ORM_Abstract/ORM_Query $object Entité à adjoindre
     * @param string $on Clause ON
     * @param string $alias Alias de la jointure
     * @return ORM_Query
     */
    public function addInnerJoin($object, $on = null, $alias = null)
    {
        return $this->addJoin('INNER', $object, $on, $alias);
    }

    /**
     * Ajout d'une jointure à la requête.
     * Si l'entité adjointe est :
     * 
     * - une chaîne
     * Si cette chaîne est un nom d'objet d'ORM existant, la table correspondant à cet objet sera utilisée en tant que jointure.
     * Si la chaîne se termine par un '|' suivi du nom d'une relation, cette relation sera utilisée en clause ON.
     * (dans ce cas, les clauses ON supplémentaires seront tout de même prises en compte)
     * 
     * Exemple :
     * @code
     * $query = $police->getQuery()
     *         ->addLeftJoin('Data_Filiale|polices', "(POL_DATEFIN IS NULL OR POL_DATEFIN >= CONVERT(DATETIME, '".date('Y-m-d')."',120))")
     *         ->addLeftJoin('Data_Garantie|police')
     *         ->setSelect(array("CASE POL_LIB_RISQUE WHEN 'Santé' THEN 1 ELSE 2 END AS IS_SANTE", 'POL_DATDEB'))
     *         ->setTop(10)
     *         ->setDistinct(true)
     *         ->setOrderBy("IS_SANTE, POL_DATDEB DESC");
     * @endcode
     * 
     * - un objet d'ORM
     * La table correspondant à cet objet sera utilisée en tant que jointure.
     * 
     * - un objet ORM_Query
     * La jointure sera la sous-requête correspondant à cet objet. Dans ce cas, un alias est obligatoire, et la clause ON ne pourra être alimentée automatiquement.
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
     * @example "ORM : requêtes et jointures via ORM_Query"
     * @param string $type Chaîne parmis les valeurs 'LEFT', 'RIGHT' ou 'INNER'
     * @param string/ORM_Abstract/ORM_Query $object Entité à adjoindre
     * @param string $on Clause ON
     * @param string $alias Alias de la jointure
     * @return ORM_Query
     * @throws ORM_Exception
     */
    private function addJoin($type, $object, $on = null, $alias = null)
    {
        $foreignKey = null;
        $table      = null;

        if ($on !== null)
        {
            if (is_string($on))
                $on = array($on);

            if (!is_array($on))
                throw new ORM_Exception("Argument 'on' : chaine ou null attendu");
        }
        else
            $on = array();

        if (is_string($object))
        {
            if (strstr($object, '|'))
            {
                list($object, $foreignKey) = explode('|', $object);

                if ($foreignKey === null)
                    throw new ORM_Exception("Foreign '$object' key invalide");
            }

            try
            {
                $object = ORM_Factory::get($object);
            }
            catch (ORM_Factory_Exception $e)
            {
                $table  = $object;
                $object = null;
            }
        }

        if (!$object)
            throw new ORM_Exception("Objet '$table' invalide : nom de classe ou objet etendant ORM_Abstract ou nom de table attendu");
        elseif ($object instanceof ORM_Abstract)
        {
            $table = $object->getTableName();
            if (!$table)
                throw new ORM_Exception("Table invalide");
            
            if (isset($alias) && is_string($alias))
                $table .= ' '.$alias;

            if ($foreignKey !== null)
            {
                $foreignkeys = $object->getForeignKeys();
                if (!isset($foreignkeys[$foreignKey]))
                    throw new ORM_Exception("Cle etrangere $foreignKey introuvable");

                $foreignKey    = $foreignkeys[$foreignKey];
                $localFields   = explode('|', $foreignKey[1]);
                $foreignFields = explode('|', $foreignKey[2]);

                foreach ($localFields as $key => $localField)
                {
                    if (!isset($foreignFields[$key]))
                        throw new ORM_Exception("Pas de correspondance pour le champ '$localField'");

                    $on[] = $foreignFields[$key] . ' = ' . $localField;
                }
            }
        }
        elseif ($object instanceof ORM_Query)
        {
            if ($alias == null || !is_string($alias))
                throw new ORM_Exception("Alias indéfini");
            else
            {
                $table = $alias;
                if ($object->getOrderBy() !== null && $object->getTop() === null)
                    $object->setOrderBy(null);
            }
        }

        $on = implode(' AND ', $on);
        if (!$on)
            throw new ORM_Exception("Jointure invalide : 'on' indéfini");

        $this->join[] = array('object' => $object, 'type'   => $type, 'table'  => $table, 'on'     => $on);

        return $this;
    }

    /**
     * Paramétrage de la clause WHERE.
     * Remplace toutes les clauses WHERE existantes.
     * 
     * @see ORM_Query::getSQLWhere() Différentes manières de fournir les clauses WHERE
     * @param string/array/ORM_Query $where
     * @return ORM_Query
     */
    public function setWhere($where)
    {
        $this->where = array();
        $this->addWhere($where);

        return $this;
    }

    /**
     * Ajout d'une clause WHERE.
     * 
     * @see ORM_Query::getSQLWhere() Différentes manières de fournir les clauses WHERE
     * @param string/array/ORM_Query $where
     * @return ORM_Query
     */
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

    /**
     * Ajoute une clause UNION à la requête.
     *
     * Le premier paramètre $select peut être un string, un objet ORM_Query
     * existant ou un array avec l'un de ces types.
     *
     * @param  array|string|ORM_Query $select Une ou plusieurs clauses pour le UNION.
     * @return ORM_Query
     */
    public function addUnion($select = array(), $type = self::SQL_UNION)
    {
        if (!is_array($select)) {
            $select = array($select);
        }

        if (!in_array($type, self::$_unionTypes)) {
            throw new ORM_Exception("Invalid union type '{$type}'");
        }

        foreach ($select as $target) {
            $this->union[self::UNION][] = array($target, $type);
        }

        return $this;
    }

    /**
     * Paramétrage de la clause de tri.
     * 
     * @param string $orderBy
     * @return ORM_Query
     */
    public function setOrderBy($orderBy)
    {
        $this->orderBy = $orderBy;
        return $this;
    }

    /**
     * Paramétrage du groupBy.
     * 
     * @param string $orderBy
     * @return ORM_Query
     */
    public function setGroupBy($groupBy)
    {
        $this->groupBy = $groupBy;
        return $this;
    }

    /**
     * Renvoie la clause de tri.
     * 
     * @return string Clause de tri
     */
    public function getOrderBy()
    {
        return $this->orderBy;
    }

    /**
     * Renvoie du nombre d'enregistrement que la requête doit sélectionner.
     * 
     * @return integer Nombre d'enregistrements
     */
    public function getTop()
    {
        return $this->top;
    }

    /**
     * Paramètrage du nombre d'enregistrement à renvoyer au parcours du jeu de résultats
     * 
     * @param integer $count Nombre d'enregistrements
     * @return ORM_Query
     */
    public function setCount($count)
    {
        $this->count = $count;
        return $this;
    }

    /**
     * Paramètrage du décalage sur le jeu de résultats
     * 
     * @param integer $count Nombre d'enregistrements dont le curseur doit se décaler
     * @return ORM_Query
     */
    public function setOffset($offset)
    {
        $this->offset = $offset;
        return $this;
    }

    /**
     * Renvoie la liste des champs sélectionnés
     * 
     * @return array Tableau de nom des champs en base de données
     */
    public function getSelect()
    {
        return $this->select;
    }
    
    /**
     * Renvoie le code SQL correspondant à la clause SELECT.
     * Gère les filtres éventuels déclarés au sein des objets d'ORM.
     * Les filtres ne sont pas gérés si votre champ est contenu dans une chaine de caractères
     * 
     * @return string Chaîne SQL
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

            // Récupération filtres de l'objet d'ORM
            $ormObjectFilters                     = $this->ormObject->getFilters();
            if (is_array($ormObjectFilters) && !empty($ormObjectFilters))
                $filters[get_class($this->ormObject)] = $ormObjectFilters;

            // Récupération filtres des jointures
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
     * Les jointures peuvent être faites sur des tables ou des sous-requêtes.
     * 
     * @return string Chaîne SQL
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
     * Activation ou désactivation de la sélection distincte (activée par défaut).
     * 
     * @param boolean $flag Si à true, sélection distincte
     * @return ORM_Query
     */
    public function setDistinct($flag = true)
    {
        $this->distinct = $flag;
        return $this;
    }

    /**
     * Renvoie le code SQL correspondant au FROM.
     * Le FROM peut être une table ou une sous-requête.
     * 
     * @return string Chaîne SQL
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
     * Renvoie le code SQL correspondant à la clause WHERE.
     * 
     * @return string Chaîne SQL
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
     * Renvoie le code SQL correspondant à la clause ORDER BY.
     *
     * @return string Chaîne SQL
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
     * Renvoie le code SQL correspondant à la clause UNION.
     *
     * @return string Chaîne SQL
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
     * Renvoie le code SQL correspondant à la clause GROUP BY.
     * 
     * @return string Chaîne SQL
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
     * Renvoie le code SQL correspondant à une sélection.
     * 
     * @return string Chaîne SQL
     */
    public function getSQLLoad()
    {
        if ($this->distinct === null)
            $this->distinct = true;

        $sql = $this->getSQLSelect() . ' ' . $this->getSQLFrom() . ' ' . $this->getSQLJoin() . ' ' . $this->getSQLWhere() . ' ' . $this->getSQLUnion() . ' ' . $this->getSQLGroupBy(). ' ' .$this->getSQLOrderBy();
        return $sql;
    }

    /**
     * Renvoie le code SQL correspondant à une suppression.
     * 
     * @return string Chaîne SQL
     */
    public function getSQLDelete()
    {
        $sql = 'DELETE ' . $this->getSQLFrom() . ' ' . $this->getSQLWhere();
        return $sql;
    }

    /**
     * Renvoie un tableau d'objets d'ORM depuis un jeu de résultats
     * 
     * @param resource $resultset Jeu de résultats
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
     * Renvoie un tableau de tableaux depuis un jeu de résultats
     * 
     * @param resource $resultset Jeu de résultats
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
     * Renvoie un tableau de paires clé => valeur depuis le jeu de résultats
     * - Si la requête ramène un champ, la clé est numérique est la valeur est le contenu du champ
     * - Si la requête ramène deux champs, la clé est la valeur du premier champ, la valeur celle du second champ
     * 
     * @param resource $resultset Jeu de résultats
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
                throw new ORM_Exception("Pour constituer une liste, la requête doit ramener 1 ou 2 champs");
        }

        return $instances;
    }

    /**
     * Renvoie le nombre de résultats correspondant à la sélection
     * 
     * @return integer Nombre de résultats
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
            throw new ORM_Exception('Problème lors du chargement d\'objet(s) ' . get_class($this->ormObject), $e);
        }
    }

    /**
     * Chargement du premier objet d'ORM renvoyé par la requête paramétrée
     * 
     * @return ORM_Abstract Objet d'ORM ou null
     * @throws ORM_Exception
     */
    
    public function loadOne($cascade = false)
    {
        try
        {
            if ($this->offset == null)
                $this->setTop(1);

            $this->setCount(1);
            $result = $this->load($cascade);

            if (count($result) == 1)
                return $result[0];
            else
                return null;
        }
        catch (BDD_Exception $e)
        {
            throw new ORM_Exception('Problème lors du chargement d\'objet(s) ' . get_class($this->ormObject), $e);
        }
    }
    
    /**
     * Chargement du tableau d'objets d'ORM renvoyés par la requête paramétrée
     * 
     * @param array $cascade
     * @return array Tableau d'objets d'ORM
     * @throws ORM_Exception
     */
    public function load($cascade = false)
    {
        try
        {
            $sql = $this->getSQLLoad();

            if (self::$cacheEnabled && isset(self::$cacheSQL['objects'][$sql]) && is_array(self::$cacheSQL['objects'][$sql]))
            {
                BDD::getConnection()->getLogObject()->info("[cache] " . $sql);
                return self::$cacheSQL['objects'][$sql];
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
                $instances = $this->fetchObjects($resultset, $cascade);
                
                if (self::$cacheEnabled) {
                    self::$cacheSQL['objects'][$sql] = $instances;
                }

                return $instances;
            }
        }
        catch (BDD_Exception $e) {
            throw new ORM_Exception('Problème lors du chargement d\'objet(s) ' . get_class($this->ormObject), $e);
        }
    }

    /**
     * Chargement du tableau de tableaux renvoyés par la requête paramétrée
     * 
     * @return array Tableau de tableaux de paires nom du champ en base de données => valeur
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
            throw new ORM_Exception('Problème lors du chargement d\'objet(s) ' . get_class($this->ormObject), $e);
        }
    }

    /**
     * Chargement d'une collection d'objets d'ORM renvoyés par la requête paramétrée
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
                //si $maxRow a été renseigné par une méthode count personnalisée (voir "load()" de ORM_Pager) alors on utilise la methode optimisé pour du pager, pour uniquement ramener les "nbRowParPage" enregistrement
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
            throw new ORM_Exception('Problème lors du chargement d\'objet(s) ' . get_class($this->ormObject), $e);
        }
    }
    
    /**
     * Chargement d'une collection d'objets d'ORM renvoyés par la procédure
     * 
     * Exemple d'appel de procedure stockée :
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
            throw new ORM_Exception('Problème lors du chargement d\'objet(s) ' . get_class($this->ormObject), $e);
        }
    }

    /**
     * Nettoyage du cache des requêtes SQL
     */
    public static function clearCache()
    {
        if (self::$cacheEnabled)
            self::$cacheSQL = array();
    }

    /**
     * Suppression des objets concernés par la requête paramétrée
     * @return null
     */
    public function delete()
    {
        self::clearCache();

        $query = $this->getSQLDelete();
        return BDD::getConnection()->execute($query);
    }

    /**
     * Echappement d'une chaîne pour préparation à la requête SQL : doublage des quotes.
     * - permet de passer des quotes dans les valeurs
     * - protège contre l'injection SQL
     * 
     * @param string $string Chaîne à échapper
     * @return string
     */
    public static function escape($string)
    {
        return str_replace(self::QUOTE_CHAR, self::QUOTE_CHAR_ESCAPED, $string);
    }

    /**
     * Mise d'une chaîne entre quote pour préparation à la requête SQL : 
     * - remplacement par NULL si la valeur est nulle
     * - mise entre quotes
     * - échappement
     * - permet de passer des quotes dans les valeurs
     * - protège contre l'injection SQL
     * 
     * @see ORM_Query::escape()
     * @param string $string Chaîne à mettre entre quotes
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
     * Renvoie un nouveau pager, initialisé sur cette requête.
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
