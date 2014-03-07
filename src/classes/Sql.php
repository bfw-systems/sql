<?php
/**
 * Classes en rapport avec les sgdb
 * @author Vermeulen Maxime
 * @version 1.0
 */

namespace BFWSql;

/**
 * Classe POO gérant la sgbd.
 * 
 * @author Vermeulen Maxime
 * @version 1.0
 * @package BFW
 */
class Sql extends \BFW\Kernel implements \BFWSqlInterface\ISql
{
    /**
     * @var $nb_query : Nombre de requête effectué
     */
    protected $nb_query;
    
    /**
     * @var @PDO : L'objet PDO
     */
    protected $PDO;
    
    /**
     * @var $modeleName : Nom de la table si c'est un modele
     */
    protected $modeleName;
    
    /**
     * @var $prefix : Le préfix des tables
     */
    protected $prefix = '';
    
    /**
     * Renvoi la valeur d'un attribut
     * @param string $name Le nom de l'argument
     */
    public function __get($name)
    {
        return $this->$name;
    }
    
    /**
     * Constructeur de la classe.
     * @param Sql_connect &$DB_connect [opt] : L'instance de la classe Sql_connect
     */
    public function __construct(&$DB_connect=null)
    {
        if($DB_connect == null)
        {
            global $DB;
            $DB_connect = $DB;
        }
        
        if(is_object($DB_connect))
        {
            $this->PDO = $DB_connect->getPDO();
            $this->nb_query = &$DB_connect->getNbQuery();
        }
        
        global $bd_prefix;
        $this->prefix = $bd_prefix;
    }
    
    /**
     * Modifie le nom de la table sur laquelle on travail
     * @param string $name : le nom de la table
     */
    public function set_modeleName($name)
    {
        $this->modeleName = $this->prefix.$name;
    }
    
    /**
     * Renvoi l'id du dernier élément ajouté en bdd
     * @param string nom de la séquence pour l'id (PostgreSQL)
     * @return int : l'id
     */
    public function der_id($name=NULL)
    {
        return $this->PDO->lastInsertId($name);
    }
    
    /**
     * Renvoi l'id du dernier élément ajouté en bdd pour une table sans Auto Incrément
     * @param string : La table
     * @param string : Le nom du champ correspondant à l'id
     * @param strng/array : Les champs sur lesquels se baser
     * @param strng/array : Clause where
     * @return int : l'id
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
     * @param string (array|objet|object) : Le type de retour qui sera à faire pour les données. Par tableau en tableau.
     * @return Sql_Select : L'instance de l'objet Sql_Select créé
     */
    public function select($type='array')
    {
        $this->nb_query++;
        return new SqlSelect($this, $type);
    }
    
    /**
     * Créer une instance de Sql_Insert permettant de faire une requête de type INSERT INTO
     * @return Sql_Insert : L'instance de l'objet Sql_Select créé
     * @param string [opt] : La table sur laquelle agir
     * @param array [opt] : Les données à ajouter : array('champSql' => 'données');
     */
    public function insert($table=null, $champs=null)
    {
        $this->nb_query++;
        return new SqlInsert($this, $table, $champs);
    }
    
    /**
     * Créer une instance de Sql_Update permettant de faire une requête de type UPDATE
     * @return Sql_Update : L'instance de l'objet Sql_Select créé
     * @param string [opt] : La table sur laquelle agir
     * @param array [opt] : Les données à modifier : array('champSql' => 'données');
     */
    public function update($table=null, $champs=null)
    {
        $this->nb_query++;
        return new SqlUpdate($this, $table, $champs);
    }
    
    /**
     * Créer une instance de Sql_Delete permettant de faire une requête de type DELETE FROM
     * @return Sql_Delete : L'instance de l'objet Sql_Select créé
     * @param string [opt] : La table sur laquelle agir
     */
    public function delete($table=null)
    {
        $this->nb_query++;
        return new SqlDelete($this, $table);
    }
    
    /**
     * Trouve le premier id libre pour une table et pour un champ
     * @param string : La table
     * @param string : Le champ
     * @return int/bool : L'id libre trouvé. False si erreur
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
     * Génère une exception s'il y a eu un échec
     * @param string : La requête à exécuter
     * @return mixed : La ressource de la requête exécuté si elle a réussi, false sinon.
     */
    public function query($requete)
    {
        $this->PDO->nb_query++;
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
}
?>