<?php
/**
 * Classes en rapport avec les sgdb
 * @author Vermeulen Maxime <bulton.fr@gmail.com>
 * @version 1.0
 */

namespace BFWSql;

use \PDOStatement;

/**
 * Classe Sql permettant de faire une requête de type Select
 * @package bfw-sql
 */
class SqlSelect extends SqlActions implements \BFWSqlInterface\ISqlSelect
{
    /**
     * @var string $typeResult Le type de retour pour les données
     */
    protected $typeResult = '';
    
    /**
     * @var PDOStatement|bool $req Le retour de la requête une fois exécutée (PDO->query();)
     */
    protected $req = false;
    
    /**
     * @var array $no_result Permet de savoir si l'echec est du à la requête qui n'a rien renvoyé ou une erreur
     */
    protected $no_result = false;
    
    /**
     * @var array $select Les champs à retournés
     */
    protected $select = array();
    
    /**
     * @var array $from Les infos sur la tables utilisé pour la partie FROM de la requête
     */
    protected $from = array();
    
    /**
     * @var array $subQuery Les sous-requêtes
     */
    protected $subQuery = array();
    
    /**
     * @var array $join Les jointures simple
     */
    protected $join = array();
    
    /**
     * @var array $joinLeft Les LEFT JOIN
     */
    protected $joinLeft = array();
    
    /**
     * @var array $joinRight Les RIGHT JOIN
     */
    protected $joinRight = array();
    
    /**
     * @var array $order Les champs pour la clause ORDER BY
     */
    protected $order = array();
    
    /**
     * @var string $limit La clause LIMIT
     */
    protected $limit = '';
    
    /**
     * @var array $limit La clause GROUP BY
     */
    protected $group = array();
    
    /**
     * Constructeur
     * 
     * @param Sql    $Sql  (ref) L'instance Sql
     * @param string $type Le type de retour pour les données. Valeurs possible : array|objet|object
     */
    public function __construct(Sql &$Sql, $type)
    {
        parent::__construct($Sql);
        $this->typeResult = $type;
    }
    
    /**
     * Accesseur pour l'attribut no_result
     * 
     * @return bool La valeur de $this->no_result
     */
    public function get_no_result()
    {
        return $this->no_result;
    }
    
    /**
     * Permet de récupérer les informations à propos de la table sur laquel on souhaite agir.
     * 
     * @param string|array $table Les infos sur la table
     * 
     * @return array les infos découpé ['tableName'] contient le nom de la table et ['as'] sont raccourcis. 
     * Si as n'a pas été indiqué, il vaux la valeur de tableName
     */
    public function infosTable($table)
    {
        //Si la table passé en paramètre est un tableau
        if(is_array($table))
        {
            $tableName = reset($table);
            $as        = key($table);
        }
        else {$as = $tableName = $table;} //Sinon, tout est égale.
        
        return array(
            'tableName' => $tableName, 
            'as'        => $as
        );
    }
    
    /**
     * Ajoute des champs pour le select
     * 
     * @param array|string $champs Les champs à ajouter.
     * @param array        &$array Le tableau auquel ajouter les champs
     * @param string       $as     Le paramètre AS pour savoir quel table
     */
    protected function addChampsToTable($champs, &$array, $as)
    {
        //S'il s'agit d'une chaine, on transforme en array
        if(!is_array($champs)) {$champs = (array) $champs;}
        
        //Liste tous les champs données en paramètre
        foreach($champs as $key => $val)
        {
            //On vérifie qu'il a des champs à ajouter. Si pas le cas, suivant.
            if($val == '') {continue;}
            
            //Si le champ est en fait une fonction, pas de quote spécial
            if(!strpos($val, '('))
            {
                //S'il s'agit d'un champ en particulier, on insert des quotes
                if($val != '*') {$val = '`'.$val.'`';}
                $val = '`'.$as.'`.'.$val;
            }
            
            //S'il y a un attribut AS
            if(is_string($key)) {$array[] = array($val, $key);}
            else {$array[] = array($val);}
        }
    }
    
    /**
     * Permet d'indiquer les infos pour le FROM
     * 
     * @param string|array $table  La table du FROM, si tableau, la clé est la valeur du AS
     * @param string       $champs (default: "*") Le ou les champs à récupérer de cette table
     * 
     * @return \BFWSql\SqlSelect L'instance de l'objet courant.
     */
    public function from($table, $champs='*')
    {
        $infosTable = $this->infosTable($table);
        $this->from = $infosTable;
        
        //Si la table à un alias (AS)
        if($infosTable['as'] != $infosTable['tableName'])
        {
            $as = $infosTable['as'];
        }
        //Si la table n'a pas d'alias, on rajoute le préfix au champs as
        else {$as = $this->prefix.$infosTable['as'];}
        
        self::addChampsToTable($champs, $this->select, $as);
        
        return $this;
    }
    
    /**
     * Permet d'indiquer les infos pour le FROM
     * 
     * @param SqlActions $req L'instance de la class SqlActions ou qui l'étends correspondant à la sous-requête
     * @param string     $as  La valeur du AS pour la sous-requête
     * 
     * @return \BFWSql\SqlSelect L'instance de l'objet courant.
     */
    public function subQuery($req, $as)
    {
        //Ajoute la requête assemblé de la sous-requête au tableau des sous requêtes
        $this->subQuery[] = array('('.$req->assemble().')', $as);
        return $this;
    }
    
    /**
     * Permet d'indiquer les infos pour la jointure
     * 
     * @param string|array $table  La table du JOIN, si tableau, la clé est la valeur du AS
     * @param string       $on     La valeur de la partie ON de la jointure
     * @param string|array $champs (default: "*") Le ou les champs à récupérer de cette table
     * 
     * @return \BFWSql\SqlSelect L'instance de l'objet courant.
     */
    public function join($table, $on, $champs='*')
    {
        return $this->abstractJoin('join', $table, $on, $champs);
    }
    
    /**
     * Permet d'indiquer les infos pour la jointure
     * 
     * @param string|array $table  La table du LEFT JOIN, si tableau, la clé est la valeur du AS
     * @param string       $on     La valeur de la partie ON de la jointure
     * @param string|array $champs (default: "*") Le ou les champs à récupérer de cette table
     * 
     * @return \BFWSql\SqlSelect L'instance de l'objet courant.
     */
    public function joinLeft($table, $on, $champs='*')
    {
        return $this->abstractJoin('joinLeft', $table, $on, $champs);
    }
    
    /**
     * Permet d'indiquer les infos pour la jointure
     * 
     * @param string|array $table  La table du RIGHT JOIN, si tableau, la clé est la valeur du AS
     * @param string       $on     La valeur de la partie ON de la jointure
     * @param string|array $champs (default: "*") Le ou les champs à récupérer de cette table
     * 
     * @return \BFWSql\SqlSelect L'instance de l'objet courant.
     */
    public function joinRight($table, $on, $champs='*')
    {
        return $this->abstractJoin('joinRight', $table, $on, $champs);
    }
    
    /**
     * Permet d'ajouter tout type de jointure géré par la classe
     * 
     * @param string       $attribute Le type de jointure. Valeurs possible "join", "joinLeft", "joinRight"
     * @param string|array $table     La table de la jointure, si tableau, la clé est la valeur du AS
     * @param string       $on        La valeur de la partie ON de la jointure
     * @param string|array $champs    (default: "*") Le ou les champs à récupérer de cette table
     * 
     * @return \BFWSql\SqlSelect L'instance de l'objet courant.
     */
    protected function abstractJoin($attribute, $table, $on, $champs)
    {
        $infosTable        = $this->infosTable($table);
        $infosTable['on']  = $on;
        $this->{$attribute}[] = $infosTable;
        
        //Ajout des champs
        self::addChampsToTable($champs, $this->select, $infosTable['as']);
        
        return $this;
    }
    
    /**
     * Permet d'ajouter une clause order by à la requête
     * 
     * @param string $cond Le champ concerné par l'order by
     * 
     * @return \BFWSql\SqlSelect L'instance de l'objet courant.
     */
    public function order($cond)
    {
        $this->order[] = $cond;
        return $this;
    }
    
    /**
     * Permet d'ajouter une clause limit à la requête
     * 
     * @param array|string $limit Soit 1 paramètre (le nombre à retourner), soit 2 paramètres (le nombre où on commence et le nombre à retourner)
     * 
     * @return \BFWSql\SqlSelect L'instance de l'objet courant.
     */
    public function limit($limit)
    {
        if(!is_array($limit)) {$limit = (array) $limit;}
        
        //S'il s'agit d'un eccart de valeur (ex: pagination)
        if(isset($limit[1])) {$this->limit = $limit[0].', '.$limit[1];}
        else {$this->limit = $limit[0];} //Juste un nombre de valeur à retourner
        
        return $this;
    }
    
    /**
     * Permet d'ajouter une clause group by à la requête
     * 
     * @param string $cond Le champ concerné par le group by
     * 
     * @return \BFWSql\SqlSelect L'instance de l'objet courant.
     */
    public function group($cond)
    {
        $this->group[] = $cond;
        return $this;
    }
    
    /**
     * 
     */
    protected function fetch()
    {
        //Exécution de la requête
        $req = $this->execute();
        
        //Si la requête est passé
        if($req) {return $req;}
        
        //Si la requête à fail et qu'on est ici, executeReq() aura levé une exception
        return false;
    }
    
    /**
     * Permet d'obtenir la constante PDO pour le format de donnée à renvoyer
     * 
     * @return integer
     */
    protected function getPdoFetchType()
    {
        //On détermine la forme souhaité
        if($this->typeResult == 'objet' || $this->typeResult == 'object')
        {
            return \PDO::FETCH_OBJ;
        }
        else {return \PDO::FETCH_ASSOC;}
    }
    
    /**
     * Retourne une seule ligne de résultat
     * 
     * @return mixed Les données sous la forme demandé, false s'il y a un problème avec la requête (l'erreur est automatiquement affiché).
     */
    public function fetchRow()
    {
        //Exécution de la requête
        $req = $this->execute();
        
        //Si la requête fail, executeReq() aura levé une exception
        if(!$req) {return false;}
        
        //On renvoi les données
        return $req->fetch($this->getPdoFetchType());
        
    }
    
    /**
     * Retourne toutes les lignes de la requête
     * 
     * @return mixed Les données sous la forme demandé mis dans un array où chaque noeud est une ligne
     * false s'il y a un problème avec la requête (l'erreur est automatiquement affiché).
     */
    public function fetchAll()
    {
        //Initialise le tableau qui récupérera tout
        $res = array();
        
        //Exécution de la requête
        //Si la requête fail, executeReq() leve une exception
        $req = $this->execute();
        
        //Si la requête fail, executeReq() aura levé une exception
        if(!$req) {return false;}
        
        //On boucle pour tout récupérer et on met tout dans une nouveau noeud de $res
        while($fetch = $req->fetch($this->getPdoFetchType()))
        {
            $res[] = $fetch;
        }
        
        //On retourne toutes les lignes
        return $res;
    }
    
    /**
     * On assemble la requête
     * 
     * @return void
     */
    public function assembler_requete()
    {
        $select = $this->generateSelect();
        $from   = $this->generateFrom();
        
        $join      = $this->generateJoin('join');
        $joinLeft  = $this->generateJoin('joinLeft');
        $joinRight = $this->generateJoin('joinRight');
        
        $where = $this->generateWhere();
        $order = $this->generateOrderBy();
        $group = $this->generateGroupBy();
        
        //Partie LIMIT
        $limit = '';
        if($this->limit != '') {$limit = ' LIMIT '.$this->limit;}
        //Fin Partie LIMIT
        
        //Et on créer la requête :)
        $this->RequeteAssembler = 'SELECT '.$select.' FROM '.$from.$join.$joinLeft.$joinRight.$where.$group.$order.$limit;
        
        $this->_kernel->notifyObserver(array('value' => 'REQ_SQL', 'REQ_SQL' => $this->RequeteAssembler));
    }
    
    /**
     * Permet de générer la partie select pour la génération de la requête
     * 
     * @return string
     */
    protected function generateSelect()
    {
        $select = '';
        foreach($this->select as $val) //On liste tous les champs
        {
            //S'il y a d'autres champs dans la clause, on met une virgule
            if($select != '') {$select .= ', ';}
            
            $select .= $val[0]; //Récupère le nom de la colonne
            if(isset($val[1])) {$select .= ' AS '.$val[1];} //l'alias s'il y en a un
        }
        
        //Cas où la table est le modèle, pas de champs indiqué : on retourne toutes les colonnes.
        if(
            $select == '' && $this->modeleName != null &&
            (
                (isset($this->from['tableName']) && $this->from['tableName'] == '') || 
                !isset($this->from['tableName'])
            )
        )
        {
            $select = '*';
        }
        
        //Sous-Requête
        if($this->subQuery != '')
        {
            foreach($this->subQuery as $val) //On liste les sous-requêtes
            {
                //S'il y a d'autres champs dans la clause, on met une virgule
                if($select != '') {$select .= ', ';}
                
                $select .= $val[0].' AS `'.$val[1].'`';
            }
        }
        //Fin Sous-Requête
        
        return $select;
    }
    
    /**
     * Permet de générer la partie from pour la génération de la requête
     * 
     * @return string
     */
    protected function generateFrom()
    {
        //Si la table n'est pas indiqué, on utilise le modèle
        if(
            $this->modeleName != null && 
            (
                (isset($this->from['tableName']) && $this->from['tableName'] == '') || 
                !isset($this->from['tableName'])
            )
        )
        {
            $this->from = array(
                'tableName' => $this->modeleName,
                'as'        => $this->modeleName
            );
        }
        else //Sinon on prend les infos de la table pour lui ajouter le préfix
        {
            //Si la table a le même nom que l'alias indiqué : On ajoute le préfix au nom et à l'alias
            if($this->from['tableName'] == $this->from['as'])
            {
                $this->from = array(
                    'tableName' => $this->prefix.$this->from['tableName'],
                    'as'        => $this->prefix.$this->from['as']
                );
            }
            //Si l'alias est différent du nom, on ajoute le préfix qu'au nom
            else {$this->from['tableName'] = $this->prefix.$this->from['tableName'];}
            
        }
        
        //Si le préfix et le nom de la table sont identique : pas besoin de AS dans la requête
        if($this->from['tableName'] == $this->from['as'])
        {
            $from = '`'.$this->from['tableName'].'`';
        }
        else {$from = '`'.$this->from['tableName'].'` AS `'.$this->from['as'].'`';}
        
        return $from;
    }
    
    /**
     * Permet d'ajouter tout type de jointure géré par la classe à la génération de la requête
     * 
     * @param string       $attribute Le type de jointure. Valeurs possible "join", "joinLeft", "joinRight"
     * 
     * @return string
     */
    protected function generateJoin($attribute)
    {
        $join = '';
        if(count($this->{$attribute}) > 0) //S'il y a une jointure inner join à faire
        {
            $condition = '';
                if($attribute == 'join')      {$condition = ' INNER JOIN ';}
            elseif($attribute == 'joinLeft')  {$condition = ' LEFT JOIN ';}
            elseif($attribute == 'joinRight') {$condition = ' RIGHT JOIN ';}
            
            //Boucle sur toutes les jointures
            foreach($this->{$attribute} as $val)
            {
                //Si le nom de la table et l'alias sont identique : On rajoute le prefix
                if($val['tableName'] == $val['as'])
                {
                    $val['tableName'] = $this->prefix.$val['tableName'];
                    $val['as']        = $this->prefix.$val['as'];
                }
                //Sinon on met le préfix que sur la table, pas sur l'alias
                else {$val['tableName'] = $this->prefix.$val['tableName'];}
                
                $join .= $condition;
                
                //Si la table et l'alias sont identique, pas besoin du AS
                if($val['tableName'] == $val['as'])
                {
                    $join .= '`'.$val['tableName'].'`';
                }
                else {$join .= '`'.$val['tableName'].'` AS `'.$val['as'].'`';}
                
                $join .= ' ON '.$val['on'];
            }
        }
        
        return $join;
    }
    
    /**
     * Permet de générer la partie order by pour la génération de la requête
     * 
     * @return string
     */
    protected function generateOrderBy()
    {
        $order = '';
        if(count($this->order) > 0) //S'il y a une partie ORDER BY
        {
            $order = ' ORDER BY ';
            //Boucle sur les conditions order
            foreach($this->order as $val)
            {
                //S'il y en a déjà une, on rajoute une virgule entre chaque
                if($order != ' ORDER BY ') {$order .= ', ';}
                $order .= $val;
            } 
        }
        
        return $order;
    }
    
    /**
     * Permet de générer la partie group by pour la génération de la requête
     * 
     * @return string
     */
    protected function generateGroupBy()
    {
        $group = '';
        if(count($this->group) > 0) //S'il y a une partie GROUP BY
        {
            $group = ' GROUP BY ';
            //Boucle sur les conditions group by
            foreach($this->group as $val)
            {
                //S'il y a déjà une condition, on rajoute une virgule entre chaque
                if($group != ' GROUP BY ') {$group .= ', ';}
                $group .= $val;
            } 
        }
        
        return $group;
    }
} 
?>