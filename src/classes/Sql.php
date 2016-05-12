<?php
/**
 * Classes en rapport avec les sgdb
 * @author Vermeulen Maxime <bulton.fr@gmail.com>
 * @version 1.0
 */

namespace BFWSql;

use \Exception;

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
     * @var \PDO L'objet PDO
     */
    protected $PDO;
    
    /**
     * @var \BFWSql\SqlConnect L'objet SqlConnect
     */
    protected $SqlConnect;
    
    /**
     * @var string|null $modeleName Nom de la table si c'est un modele
     */
    protected $modeleName;
    
    /**
     * @var string $prefix Le préfix des tables
     */
    protected $prefix = '';
    
    /**
     * Accesseur get vers l'attribut $PDO
     * 
     * @return \PDO
     */
    public function getPDO()
    {
        return $this->PDO;
    }
    
    /**
     * Accesseur get vers l'attribut $SqlConnect
     * 
     * @return \BFWSql\SqlConnect
     */
    public function getSqlConnect()
    {
        return $this->SqlConnect;
    }
    
    /**
     * Accesseur get vers l'attribut $modeleName
     * 
     * @return string
     */
    public function getModeleName()
    {
        return $this->modeleName;
    }
    
    /**
     * Accesseur get vers l'attribut $prefix
     * 
     * @return string
     */
    public function getPrefix()
    {
        return $this->prefix;
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
        
        if($DB_connect === null)
        {
            global $DB;
            $DB_connect = $DB;
        }
        
        $this->PDO = null;
        $this->SqlConnect = null;
        
        if(is_object($DB_connect))
        {
            $this->PDO = $DB_connect->getPDO();
            $this->SqlConnect = &$DB_connect;
        }
        else {throw new Exception('La variable vers la connexion à la bdd doit être un objet.');}
        
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
     * @return integer
     */
    public function der_id($name=null)
    {
        return (int) $this->PDO->lastInsertId($name);
    }
    
    /**
     * Renvoi l'id du dernier élément ajouté en bdd pour une table sans Auto Incrément
     * 
     * @param string       $table   La table
     * @param string       $champID Le nom du champ correspondant à l'id
     * @param string|array $order   Les champs sur lesquels se baser
     * @param string|array $where   Clause where
     * 
     * @return integer le dernier id, 0 si aucun résultat
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
        $req->closeCursor();
        
        if($res)
        {
            return (int) $res[$champID];
        }
        
        return 0;
    }
    
    /**
     * Créer une instance de Sql_Select permettant de faire une requête de type SELECT
     * 
     * @param string $type (default: "array") Le type de retour pour les données. Valeurs possible : array|objet|object
     * 
     * @return \BFWSql\SqlSelect L'instance de l'objet Sql_Select créé
     */
    public function select($type='array')
    {
        return new SqlSelect($this, $type);
    }
    
    /**
     * Créer une instance de Sql_Insert permettant de faire une requête de type INSERT INTO
     * 
     * @param string $table  (default: null) La table sur laquelle agir
     * @param array  $champs (default: null) Les données à ajouter : array('champSql' => 'données');
     * 
     * @return \BFWSql\SqlInsert L'instance de l'objet Sql_Select créé
     */
    public function insert($table=null, $champs=null)
    {
        return new SqlInsert($this, $table, $champs);
    }
    
    /**
     * Créer une instance de Sql_Update permettant de faire une requête de type UPDATE
     * 
     * @param string $table  (default: null) La table sur laquelle agir
     * @param array  $champs (default: null) Les données à ajouter : array('champSql' => 'données');
     * 
     * @return \BFWSql\SqlUpdate L'instance de l'objet Sql_Select créé
     */
    public function update($table=null, $champs=null)
    {
        return new SqlUpdate($this, $table, $champs);
    }
    
    /**
     * Créer une instance de Sql_Delete permettant de faire une requête de type DELETE FROM
     * 
     * @param string $table (default: null) La table sur laquelle agir
     * 
     * @return \BFWSql\SqlDelete L'instance de l'objet Sql_Select créé
     */
    public function delete($table=null)
    {
        return new SqlDelete($this, $table);
    }
    
    /**
     * Trouve le premier id libre pour une table et pour un champ
     * 
     * @param string $table La table
     * @param string $champ Le champ. Les valeurs du champ doivent être du type int.
     * 
     * @throws \Exception Si ue erreur dans la recherche d'id s'est produite
     * 
     * @return integer L'id libre trouvé. False si erreur
     */
    public function create_id($table, $champ)
    {
        $req = $this->select()->from($table, $champ)->order($champ.' ASC')->limit(1);
        $res = $req->fetchRow();
        $req->closeCursor();
        
        if($res)
        {
            if($res[$champ] == 1)
            {
                $req2 = $this->select()->from($table, $champ)->order($champ.' DESC')->limit(1);
                $res2 = $req2->fetchRow();
                $req2->closeCursor();
                
                //Exception levé si $res2 == false.
                return $res2[$champ]+1;
            }
            
            return $res[$champ]-1;
        }
        // Pas de else car exception levé.
        
        return 1;
    }
    
    /**
     * Execute la requête mise en paramètre
     * 
     * @param string $requete La requête à exécuter
     * 
     * @throws \Exception Si la requête à echoué
     * 
     * @return \PDOStatement La ressource de la requête exécuté si elle a réussi.
     */
    public function query($requete)
    {
        $this->upNbQuery();
        $req = $this->PDO->query($requete); //On exécute la reqête
        
        //On récupère l'erreur
        $erreur = $this->PDO->errorInfo();
        
        //On créé l'exception que s'il y a véritablement une erreur.
        if(!$req && $erreur[0] != null && $erreur[0] != '00000' && isset($erreur[2])) 
        {
            throw new Exception($erreur[2]);
        }
        
        //Si la requête à réussi, on retourne sa ressource
        return $req;
    }
    
    /**
     * Incrémente le nombre de requête effectué
     * 
     * @return void
     */
    public function upNbQuery()
    {
        $this->SqlConnect->upNbQuery();
    }
    
    /**
     * Accesseur pour accéder au nombre de requête
     * 
     * @return integer Le nombre de requête
     */
    public function getNbQuery()
    {
        return $this->SqlConnect->getNbQuery();
    }
}
?>