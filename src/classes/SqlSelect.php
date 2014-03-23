<?php
/**
 * Classes en rapport avec les sgdb
 * @author Vermeulen Maxime <bulton.fr@gmail.com>
 * @version 1.0
 */

namespace BFWSql;

/**
 * Classe Sql permettant de faire une requête de type Select
 * @package bfw-sql
 */
class SqlSelect extends SqlActions implements \BFWSqlInterface\ISqlSelect
{
    /**
     * @var $typeResult Le type de retour pour les données
     */
    protected $typeResult = '';
    
    /**
     * @var $req Le retour de la requête une fois exécutée (PDO->query();)
     */
    protected $req = false;
    
    /**
     * @var $no_result Permet de savoir si l'echec est du à la requête qui n'a rien renvoyé ou une erreur
     */
    protected $no_result = false;
    
    /**
     * @var $select Les champs à retournés
     */
    protected $select = array();
    
    /**
     * @var $from Les infos sur la tables utilisé pour la partie FROM de la requête
     */
    protected $from = array();
    
    /**
     * @var $subQuery Les sous-requêtes
     */
    protected $subQuery = array();
    
    /**
     * @var $join Les jointures simple
     */
    protected $join = array();
    
    /**
     * @var $joinLeft Les LEFT JOIN
     */
    protected $joinLeft = array();
    
    /**
     * @var $joinRight Les RIGHT JOIN
     */
    protected $joinRight = array();
    
    /**
     * @var $order Les champs pour la clause ORDER BY
     */
    protected $order = array();
    
    /**
     * @var $limit La clause LIMIT
     */
    protected $limit = '';
    
    /**
     * @var $limit La clause GROUP BY
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
        parent::__construct();
        
        $this->PDO = &$Sql->PDO;
        $this->prefix = $Sql->prefix;
        $this->modeleName = $Sql->modeleName;
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
     * On assemble la requête
     */
    public function assembler_requete()
    {
        //Partie SELECT
        $select = '';
        foreach($this->select as $val) //On liste tous les champs
        {
            if($select != '')
            {
                $select .= ', ';
            }
            
            $select .= $val[0];
            if(isset($val[1]))
            {
                $select .= ' AS '.$val[1];
            }
        }
        
        //Retourne tous le modèle
        if($select == '' && $this->from['tableName'] == '' && $this->modeleName != null)
        {
            $select = '*';
        }
        
        //Sous-Requête
        $subQuery = '';
        if($this->subQuery != '')
        {
            foreach($this->subQuery as $val) //On liste les sous-requêtes
            {
                if($select != '')
                {
                    $select .= ', ';
                }
                
                $select .= $val[0].' AS `'.$val[1].'`';
            }
        }
        //Fin Sous-Requête
        //Fin Partie SELECT
        
        //Partie FROM
            //Gestion des modeles
            if($this->from['tableName'] == '' && $this->modeleName != null)
            {
                $from = array(
                    'tableName' => $this->modeleName,
                    'as' => $this->modeleName
                );
            }
            else
            {
                if($this->from['tableName'] == $this->from['as'])
                {
                    $this->from['tableName'] = $this->prefix.$this->from['tableName'];
                    $this->from['as'] = $this->prefix.$this->from['as'];
                }
                else
                {
                    $this->from['tableName'] = $this->prefix.$this->from['tableName'];
                }
                
            }
        
        if($this->from['tableName'] == $this->from['as'])
        {
            $from = '`'.$this->from['tableName'].'`';
        }
        else //avec le AS
        {
            $from = '`'.$this->from['tableName'].'` AS `'.$this->from['as'].'`';
        }
        //Fin Partie FROM
        
        //Partie INNER JOIN
        $join = '';
        if(count($this->join) > 0)
        {
            foreach($this->join as $val)
            {
                if($join != '')
                {
                    $join .= ' ';
                }
                
                if($val['tableName'] == $val['as'])
                {
                    $val['tableName'] = $this->prefix.$val['tableName'];
                    $val['as'] = $this->prefix.$val['as'];
                }
                else
                {
                    $val['tableName'] = $this->prefix.$val['tableName'];
                }
                
                $join .= ' INNER JOIN ';
                
                if($val['tableName'] == $val['as'])
                {
                    $join .= '`'.$val['tableName'].'`';
                }
                else
                {
                    $join .= '`'.$val['tableName'].'` AS `'.$val['as'].'`';
                }
                
                $join .= ' ON '.$val['on'];
            }
        }
        //Fin Partie INNER JOIN
        
        //Partie LEFT JOIN
        $joinLeft = '';
        if(count($this->joinLeft) > 0)
        {
            foreach($this->joinLeft as $val)
            {
                if($joinLeft != '')
                {
                    $joinLeft .= ' ';
                }
                
                if($val['tableName'] == $val['as'])
                {
                    $val['tableName'] = $this->prefix.$val['tableName'];
                    $val['as'] = $this->prefix.$val['as'];
                }
                else
                {
                    $val['tableName'] = $this->prefix.$val['tableName'];
                }
                
                $joinLeft .= ' LEFT JOIN ';
                if($val['tableName'] == $val['as'])
                {
                    $joinLeft .= '`'.$val['tableName'].'`';
                }
                else
                {
                    $joinLeft .= '`'.$val['tableName'].'` AS `'.$val['as'].'`';
                }
                
                $joinLeft .= ' ON '.$val['on'];
            }
        }
        //Fin Partie LEFT JOIN
        
        //Partie RIGHT JOIN
        $joinRight = '';
        if(count($this->joinRight) > 0)
        {
            foreach($this->joinRight as $val)
            {
                if($joinRight != '')
                {
                    $joinRight .= ' ';
                }
                
                if($val['tableName'] == $val['as'])
                {
                    $val['tableName'] = $this->prefix.$val['tableName'];
                    $val['as'] = $this->prefix.$val['as'];
                }
                else
                {
                    $val['tableName'] = $this->prefix.$val['tableName'];
                }
                
                $joinRight .= ' RIGHT JOIN ';
                if($val['tableName'] == $val['as'])
                {
                    $joinRight .= '`'.$val['tableName'].'`';
                }
                else
                {
                    $joinRight .= '`'.$val['tableName'].'` AS `'.$val['as'].'`';
                }
                
                $joinRight .= ' ON '.$val['on'];
            }
        }
        //Fin Partie RIGHT JOIN
        
        //Partie WHERE
        $where = '';
        if(count($this->where) > 0)
        {
            $where = ' WHERE ';
            foreach($this->where as $val)
            {
                if($where != ' WHERE ')
                {
                    $where .= ' AND ';
                }
                $where .= $val;
            } 
        }
        //Fin Partie WHERE
        
        //Partie ORDER BY
        $order = '';
        if(count($this->order) > 0)
        {
            $order = ' ORDER BY ';
            foreach($this->order as $val)
            {
                if($order != ' ORDER BY ')
                {
                    $order .= ', ';
                }
                $order .= $val;
            } 
        }
        //Fin Partie ORDER BY
        
        //Partie GROUP BY
        $group = '';
        if(count($this->group) > 0)
        {
            $group = ' GROUP BY ';
            foreach($this->group as $val)
            {
                if($group != ' GROUP BY ')
                {
                    $group .= ', ';
                }
                $group .= $val;
            } 
        }
        //Fin Partie GROUP BY
        
        //Partie LIMIT
        $limit = '';
        if($this->limit != '')
        {
            $limit = ' LIMIT '.$this->limit;
        }
        //Fin Partie LIMIT
        
        //Et on créer la requête :)
        $this->RequeteAssembler = 'SELECT '.$select.' FROM '.$from.$join.$joinLeft.$joinRight.$where.$group.$order.$limit;
        
        $this->_kernel->notifyObserver(array('value' => 'REQ_SQL', 'REQ_SQL' => $this->RequeteAssembler));
    }
    
    /**
     * Permet de récupérer les informations à propos de la table sur laquel on souhaite agir.
     * 
     * @param array $table Les infos sur la table
     * 
     * @return array les infos découpé ['tableName'] contient le nom de la table et ['as'] sont raccourcis. 
     * Si as n'a pas été indiqué, il vaux la valeur de tableName
     */
    public function infosTable($table)
    {
        if(is_array($table))
        {
            foreach($table as $key => $val)
            {
                $as = $key;
                $tableName = $val;
            }
        }
        else
        {
            $as = $tableName = $table;
        }
                
        return array('tableName' => $tableName, 'as' => $as);
    }
    
    /**
     * Ajoute des champs pour le select
     * 
     * @param array  $champs Les champs à ajouter.
     * @param array  $array  (ref) Le tableau auquel ajouter les champs
     * @param string $as     Le paramètre AS pour savoir quel table
     */
    public function addChamps($champs, &$array, $as)
    {
        if(is_array($champs))
        {
            foreach($champs as $key => $val)
            {
                if(strpos($val, '('))
                {
                    if(is_string($key))
                    {
                        $array[] = array($val, $key);
                    }
                    else
                    {
                        $array[] = array($val);
                    }
                }
                else
                {
                    if($val != '*')
                    {
                        $val = '`'.$val.'`';
                    }
                    
                    if(is_string($key))
                    {
                        $array[] = array('`'.$as.'`.'.$val, $key);
                    }
                    else
                    {
                        $array[] = array('`'.$as.'`.'.$val);
                    }
                }
            }
        }
        else
        {
            if($champs != '')
            {
                if(strpos($champs, '('))
                {
                    $array[] = array($champs);
                }
                else
                {
                    if($champs != '*')
                    {
                        $array[] = array('`'.$as.'`.`'.$champs.'`');
                    }
                    else
                    {
                        $array[] = array('`'.$as.'`.'.$champs);
                    }
                }
            }
        }
    }
    
    /**
     * Permet d'indiquer les infos pour le FROM
     * 
     * @param string|array $table  La table du FROM, si tableau, la clé est la valeur du AS
     * @param string|array $champs (default: "*") Le ou les champs à récupérer de cette table
     * 
     * @return Sql_Select L'instance de l'objet courant.
     */
    public function from($table, $champs='*')
    {
        $infosTable = $this->infosTable($table);
        $this->from = $infosTable;
        
        if($infosTable['as'] != $infosTable['tableName'])
        {
            $as = $infosTable['as'];
        }
        else
        {
            $as = $this->prefix.$infosTable['as'];
        }
        
        $this->addChamps($champs, $this->select, $as);
        
        return $this;
    }
    
    /**
     * Permet d'indiquer les infos pour le FROM
     * 
     * @param SqlSelect|SqlUpdate|SqlInsert|SqlDelete $req L'instance Sql_Select de la sous-requête
     * @param string                                  $as  La valeur du AS pour la sous-requête
     * 
     * @return Sql_Select L'instance de l'objet courant.
     */
    public function subQuery($req, $as)
    {
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
     * @return Sql_Select L'instance de l'objet courant.
     */
    public function join($table, $on, $champs='*')
    {
        $infosTable = $this->infosTable($table);
        $infosTable['on'] = $on;
        $this->join[] = $infosTable;
        
        $this->addChamps($champs, $this->select, $infosTable['as']);
        
        return $this;
    }
    
    /**
     * Permet d'indiquer les infos pour la jointure
     * 
     * @param string|array $table  La table du LEFT JOIN, si tableau, la clé est la valeur du AS
     * @param string       $on     La valeur de la partie ON de la jointure
     * @param string|array $champs (default: "*") Le ou les champs à récupérer de cette table
     * 
     * @return Sql_Select L'instance de l'objet courant.
     */
    public function joinLeft($table, $on, $champs='*')
    {
        $infosTable = $this->infosTable($table);
        $infosTable['on'] = $on;
        $this->joinLeft[] = $infosTable;
        
        $this->addChamps($champs, $this->select, $infosTable['as']);
        
        return $this;
    }
    
    /**
     * Permet d'indiquer les infos pour la jointure
     * 
     * @param string|array $table  La table du RIGHT JOIN, si tableau, la clé est la valeur du AS
     * @param string       $on     La valeur de la partie ON de la jointure
     * @param string|array $champs (default: "*") Le ou les champs à récupérer de cette table
     * 
     * @return Sql_Select L'instance de l'objet courant.
     */
    public function joinRight($table, $on, $champs='*')
    {
        $infosTable = $this->infosTable($table);
        $infosTable['on'] = $on;
        $this->joinRight[] = $infosTable;
        
        $this->addChamps($champs, $this->select, $infosTable['as']);
        
        return $this;
    }
    
    /**
     * Permet d'ajouter une clause order by à la requête
     * 
     * @param string $cond Le champ concerné par l'order by
     * 
     * @return Sql_Select L'instance de l'objet courant.
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
     * @return Sql_Select L'instance de l'objet courant.
     */
    public function limit($limit)
    {
        if(is_array($limit))
        {
            if(isset($limit[1]))
            {
                $this->limit = $limit[0].', '.$limit[1];
            }
            else
            {
                $this->limit = $limit[0];
            }
        }
        else
        {
            $this->limit = $limit;
        }
        
        return $this;
    }
    
    /**
     * Permet d'ajouter une clause group by à la requête
     * 
     * @param string $cond Le champ concerné par le group by
     * 
     * @return Sql_Select L'instance de l'objet courant.
     */
    public function group($cond)
    {
        $this->group[] = $cond;
        return $this;
    }
    
    /**
     * Execute la requête généré
     * 
     * @throws \Exception Si la requête à echoué
     * 
     * @return \PDOStatement|bool : La ressource de la requête exécuté si elle a réussi, false sinon.
     */
    protected function executeReq()
    {
        $this->PDO->nb_query++;
        $this->is_Assembler(); //On vérifie que la requête est bien généré
        
        if($this->prepareBool)
        {
            $req = $this->PDO->prepare($this->RequeteAssembler, $this->prepare_option);
            $req->execute($this->prepare);
            $erreur = $req->errorInfo();
        }
        else
        {
            $req = $this->PDO->query($this->RequeteAssembler); //On exécute la requête
            $erreur = $this->PDO->errorInfo();
        }
        $this->req = $req;
        
        if($erreur[0] != null && $erreur[0] != '00000')
        {
            throw new \Exception($erreur[2]);
        }
        else
        {
            if($req) //Si la requête à réussi, on retourne sa ressource
            {
                if($this->nb_result() > 0)
                {
                    return $req;
                }
                else
                {
                    $this->no_result = true;
                }
            }
        }

        return false;
    }
    
    /**
     * Retourne une seule ligne de résultat
     * 
     * @return mixed Les données sous la forme demandé, false s'il y a un problème avec la requête (l'erreur est automatiquement affiché).
     */
    public function fetchRow()
    {
        try //On exécute la requête
        {
            $req = $this->executeReq();
        }
        catch(\Exception $e) //S'il y a un problème on affiche l'erreur
        {
            die ($e->getMessage());
        }
        
        if($req) //Si la requête est passé
        {
            //On récupère les résultats sous la forme demandée
            if($this->typeResult == 'objet' || $this->typeResult == 'object')
            {
                $res = $req->fetch(\PDO::FETCH_OBJ);
            }
            else
            {
                $res = $req->fetch(\PDO::FETCH_ASSOC);
            }
            
            return $res; //On renvoi les données
        }
        else //Sinon il y a eu un souci donc on renvoi false.
        {
            return false;
        }
    }
    
    /**
     * Retourne toutes les lignes de la requête
     * 
     * @return mixed Les données sous la forme demandé mis dans un array où chaque noeud est une ligne
     * false s'il y a un problème avec la requête (l'erreur est automatiquement affiché).
     */
    public function fetchAll()
    {
        $res = array();
        
        try //On exécute la requête
        {
            $req = $this->executeReq();
        }
        catch(\Exception $e) //S'il y a un problème on affiche l'erreur
        {
            die ($e->getMessage());
        }
        
        if($req) //Si la requête est passé
        {
            //On détermine la forme souhaité
            if($this->typeResult == 'objet' || $this->typeResult == 'object')
            {
                $type = \PDO::FETCH_OBJ;
            }
            else
            {
                $type = \PDO::FETCH_ASSOC;
            }
            
            //On boucle pour tout récupérer et on met tout dans une nouveau noeud de $res
            while($fetch = $req->fetch($type))
            {
                $res[] = $fetch;
            }
            
            return $res; //On retourne toutes les lignes
        }
        else //Sinon il y a eu un souci donc on renvoi false.
        {
            return false;
        }
    }
    
    /**
     * Retourne le nombre de ligne retourner par la requête
     * 
     * @return int|bool le nombre de ligne. false si ça a échoué.
     */
    public function nb_result()
    {
        if($this->req != false)
        {
            return $this->req->rowCount();
        }
        else
        {
            return false;
        }
    }
} 
?>