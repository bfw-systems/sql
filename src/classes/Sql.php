<?php
/**
 * Classes en rapport avec les sgdb
 * @author Vermeulen Maxime <bulton.fr@gmail.com>
 * @version 1.0
 */

namespace BFWSql;

/**
 * Classe POO gérant la sgbd.
 * @package bfw-sql
 */
class Sql implements \BFWSqlInterface\ISql
{
    /**
     * @var $_kernel : L'instance du Kernel
     */
    protected $_kernel;
    
    /**
     * @var $nb_query Nombre de requête effectué
     */
    protected $nb_query;
    
    /**
     * @var PDO L'objet PDO
     */
    protected $PDO;
    
    /**
     * @var SqlConnect L'objet SqlConnect
     */
    protected $SqlConnect;
    
    /**
     * @var $modeleName Nom de la table si c'est un modele
     */
    protected $modeleName;
    
    /**
     * @var $prefix Le préfix des tables
     */
    protected $prefix = '';
    
    /**
     * Renvoi la valeur d'un attribut
     * 
     * @param string $name Le nom de l'argument
     */
    public function __get($name)
    {
        return $this->$name;
    }
    
    /**
     * Constructeur de la classe.
     * 
     * @param Sql_connect|null $DB_connect (ref) (default: null) L'instance de la classe Sql_connect. Si elle n'est pas indiqué, elle sera créé.
     * 
     * @throws \Exception
     */
    public function __construct(&$DB_connect=null)
    {
        $this->_kernel = getKernel();
        
        if($DB_connect == null)
        {
            global $DB;
            $DB_connect = $DB;
        }
        
        if(is_object($DB_connect))
        {
            $this->PDO = $DB_connect->getPDO();
            $this->SqlConnect = &$DB_connect;
        }
        else {throw new \Exception('La variable vers la connexion à la bdd doit être un objet.');}
        
        global $bd_prefix;
        $this->prefix = $bd_prefix;
    }
    
    /**
     * Modifie le nom de la table sur laquelle on travail
     * 
     * @param string $name le nom de la table
     * 
     * @return string : Le nom réel de la table avec préfix s'il y en a un de défini.
     */
    public function set_modeleName($name)
    {
        $this->modeleName = $this->prefix.$name;
        return $this->modeleName;
    }
    
    /**
     * Renvoi l'id du dernier élément ajouté en bdd
     * 
     * @param string|null $name (default: null) nom de la séquence pour l'id (pour PostgreSQL par exemple)
     * 
     * @return int
     */
    public function der_id($name=null)
    {
        return $this->PDO->lastInsertId($name);
    }
    
    /**
     * Renvoi l'id du dernier élément ajouté en bdd pour une table sans Auto Incrément
     * 
     * @param string       $table   La table
     * @param string       $champID Le nom du champ correspondant à l'id
     * @param string|array $order   Les champs sur lesquels se baser
     * @param string|array $where   Clause where
     * 
     * @return int|bool l'id, false si aucun résultat
     */
    public function der_id_noAI($table, $champID, $order, $where='')
    {
        $req = $this->select()->from($table, $champID)->limit(1);
    
        if(is_array($where))
        {
            foreach($where as $val)
            {
                $req->where($val);
            }
        }
        elseif($where != '')
        {
            $req->where($where);
        }
        
        if(is_array($order))
        {
            foreach($order as $val)
            {
                $req->order($val);
            }
        }
        else
        {
            $req->order($order);
        }
        
        $res = $req->fetchRow();
        if($res)
        {
            return $res[$champID];
        }
        else
        {
            if($req->get_no_result())
            {
                return 0;
            }
            else
            {
                return false;
            }
        }
    }
    
    /**
     * Créer une instance de Sql_Select permettant de faire une requête de type SELECT
     * 
     * @param string $type (default: "array") Le type de retour pour les données. Valeurs possible : array|objet|object
     * 
     * @return Sql_Select L'instance de l'objet Sql_Select créé
     */
    public function select($type='array')
    {
        $this->nb_query++;
        return new SqlSelect($this, $type);
    }
    
    /**
     * Créer une instance de Sql_Insert permettant de faire une requête de type INSERT INTO
     * 
     * @param string $table  (default: null) La table sur laquelle agir
     * @param array  $champs (default: null) Les données à ajouter : array('champSql' => 'données');
     * 
     * @return Sql_Insert L'instance de l'objet Sql_Select créé
     */
    public function insert($table=null, $champs=null)
    {
        $this->nb_query++;
        return new SqlInsert($this, $table, $champs);
    }
    
    /**
     * Créer une instance de Sql_Update permettant de faire une requête de type UPDATE
     * 
     * @param string $table  (default: null) La table sur laquelle agir
     * @param array  $champs (default: null) Les données à ajouter : array('champSql' => 'données');
     * 
     * @return Sql_Update L'instance de l'objet Sql_Select créé
     */
    public function update($table=null, $champs=null)
    {
        $this->nb_query++;
        return new SqlUpdate($this, $table, $champs);
    }
    
    /**
     * Créer une instance de Sql_Delete permettant de faire une requête de type DELETE FROM
     * 
     * @param string $table (default: null) La table sur laquelle agir
     * 
     * @return Sql_Delete L'instance de l'objet Sql_Select créé
     */
    public function delete($table=null)
    {
        $this->nb_query++;
        return new SqlDelete($this, $table);
    }
    
    /**
     * Trouve le premier id libre pour une table et pour un champ
     * 
     * @param string $table La table
     * @param string $champ Le champ. Les valeurs du champ doivent être du type int.
     * 
     * @return int/bool L'id libre trouvé. False si erreur
     */
    public function create_id($table, $champ)
    {
        $req = $this->select()->from($table, $champ)->order($champ.' ASC')->limit(1);
        $res = $req->fetchRow();
        
        if($res)
        {
            if($res[$champ] != 1)
            {
                return $res[$champ]-1;
            }
            else
            {
                $req2 = $this->select()->from($table, $champ)->order($champ.' DESC')->limit(1);
                $res2 = $req2->fetchRow();
                
                if($res2)
                {
                    return $res2[$champ]+1;
                }
                else
                {
                    return false;
                }
            }
        }
        else
        {
            if($req->get_no_result() == true)
            {
                return 1;
            }
            else
            {
                return false;
            }
        }
    }
    
    /**
     * Execute la requête mise en paramètre
     * 
     * @param string $requete La requête à exécuter
     * 
     * @throws \Exception Si la requête à echoué
     * 
     * @return \PDOStatement|bool La ressource de la requête exécuté si elle a réussi, false sinon.
     */
    public function query($requete)
    {
        $this->upNbQuery();
        $req = $this->PDO->query($requete); //On exécute la reqête
        
        if($req) //Si la requête à réussi, on retourne sa ressource
        {
            return $req;
        }
        else
        {
            //On récupère l'erreur
            $erreur = $this->PDO->errorInfo();
            
            if($erreur[0] != 0000) //On créé l'exception si on peut récupérer les infos de l'erreur
            {
                throw new \Exception($erreur[2]);
            }
            return false; //On retourne false car échec.
        }
    }
    
    /**
     * Incrémente le nombre de requête effectué
     */
    public function upNbQuery()
    {
        $this->SqlConnect->upNbQuery();
    }
}
?>