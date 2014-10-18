<?php
/**
 * Classes en rapport avec les sgdb
 * @author Vermeulen Maxime <bulton.fr@gmail.com>
 * @version 1.0
 */

namespace BFWSql;

/**
 * Classe PDO faisait la connexion
 * @package bfw-sql
 */
class SqlConnect implements \BFWSqlInterface\ISqlConnect
{
    /**
     * @var $_kernel L'instance du Kernel
     */
    protected $_kernel;
    
    /**
     * @var $debug Si on est en mode débug ou non 
     * (utile pour afficher les erreurs ou non suivant si on est en prod ou pas)
     */
    protected $debug;
    
    /**
     * @var $type Type de connexion (mysql/pgsql/etc)
     */
    protected $type;
    
    /**
     * @var $nb_query (default: 0) Nombre de requête effectué
     */
    protected $nb_query = 0;
    
    /**
     * @var $PDO L'objet PDO
     */
    protected $PDO;
    
    /**
     * Constructeur de la classe. Créer la connexion
     * 
     * @param string $host  Adresse du serveur hôte
     * @param string $login Le login de connexion
     * @param string $passe Le mot de passe de connexion
     * @param string $base  Le nom de la base à laquelle se connecter
     * @param string $type  (default: "mysql") Le type de base à laquel on se connexion (pgsql/mysql/etc) au format de pdo
     * @param bool   $utf8  (default: "true") Si on a la base en utf8 ou non (par défaut : true)
     */
    public function __construct($host, $login, $passe, $base, $type='mysql', $utf8=true)
    {
        $this->_kernel = getKernel();
        $this->debug = $this->_kernel->get_debug();

        $this->type = $type;
        $this->PDO = new \PDO($type.':host='.$host.';dbname='.$base, $login, $passe);
        //PDO create \Exception if fail.
        
        if($utf8)
        {
            $this->set_utf8();
        }
    }
    
    /**
     * Permet d'utiliser la base sql en utf8
     * 
     * @return void
     */
    protected function set_utf8()
    {
        $this->PDO->exec('SET NAMES utf8');
    }
    
    /**
     * Permet de protéger une requête contre les injections et autres.
     * 
     * @param string $string la requêtre sql
     * 
     * @return string la requête sql protégé
     */
    public function protect($string)
    {
        $prot = $this->PDO->quote($string);
        
        //quote complique les requêtes et les conditions en rajoutant des guillemet 
        //en début et fin de chaine donc on les supprime
        $ret = substr($prot, 1, strlen($prot)-2);
        return $ret;
    }
    
    /**
     * Accesseur pour accéder à $this->PDO
     * 
     * @return \PDO Instance de la classe PDO
     */
    public function getPDO()
    {
        return $this->PDO;
    }
    
    /**
     * Accesseur pour accéder à $this->nb_query
     * 
     * @return integer Le nombre de requête
     */
    public function getNbQuery()
    {
        return $this->nb_query;
    }
    
    /**
     * Incrémente le nombre de requête effectué
     * 
     * @return void
     */
    public function upNbQuery()
    {
        $this->nb_query++;
    }
}
?>
