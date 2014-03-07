<?php
/**
 * Classes en rapport avec les sgdb
 * @author Vermeulen Maxime
 * @version 1.0
 */

namespace BFWSql;

/**
 * Classe parent aux classes Sql_Select, Sql_Insert, Sql_Update et Sql_Delete
 * Elle stock l'instance de pdo et définie quelques méthodes.
 * 
 * @author Vermeulen Maxime
 * @package BFW
 * @version 1.0
 */
class SqlActions extends \BFW\Kernel implements \BFWSqlInterface\ISqlActions
{
    /**
     * @var $PDO : L'instance de pdo
     */
    protected $PDO;
    
    /**
     * @var $RequeteAssembler : La requête final qui sera exécutée
     */
    protected $RequeteAssembler = '';
    
    /**
     * @var $modeleName : Le nom de la table du modele
     */
    protected $modeleName = null;
    
    /**
     * @var $prefix : Le préfix des tables
     */
    protected $prefix;
    
    /**
     * @var $prepare : Permet de savoir si on utilise les requêtes préparées ou non
     */
    protected $prepareBool = true;
    
    /**
     * @var $table : La table sur laquel agir
     */
    protected $table = '';
    
    /**
     * @var $champs : Les données à insérer
     */
    protected $champs = array();
    
    /**
     * @var $where : Les clauses where
     */
    protected $where = array();
    
    /**
     * @var $prepare : Les arguments de la requête préparée
     */
    protected $prepare = array();
    
    /**
     * @var $prepare_option : Les options pour la requête préparée
     */
    protected $prepare_option = array();
    
    /**
     * Constructeur de la classe
     */
    public function __construct()
    {
        global $Kernel;
        $this->set_observers($Kernel->get_observers());
    }
    
    /**
     * Permet de vérifier si la requête finale a été assemblé et si ce n'est pas le cas de lancer l'assemblage.
     */
    public function is_Assembler()
    {
        if($this->RequeteAssembler == '')
        {
            $this->assembler_requete();
        }
    }
    
    /**
     * Retourne la requête finale
     */
    public function assemble()
    {
        $this->is_Assembler();
        return $this->RequeteAssembler;
    }
    
    /**
     * Execute la requête (type INSERT, UPDATE et DELETE)
     * @return mixed : La ressource de la requête exécuté si elle a réussi, false sinon (avec une Exception).
     */
    public function execute()
    {
        $this->PDO->nb_query++;
        $this->is_Assembler();
        
        if($this->prepareBool)
        {
            $req = $this->PDO->prepare($this->RequeteAssembler, $this->prepare_option);
            $req->execute($this->prepare);
        }
        else
        {
            $req = $this->PDO->exec($this->RequeteAssembler);
        }
        
        if($req)
        {
            return $req;
        }
        else
        {
            $erreur = $this->PDO->errorInfo();
            if($erreur[0] != 0000)
            {
                die($erreur[2]);
            }
            return false;
        }
    }
    
    /**
     * Permet d'inserer sa propre requête directement sans avoir à utiliser les méthodes from etc
     * @param string : La requête
     */
    public function query($req)
    {
        $this->RequeteAssembler = $req;
    }
    
    /**
     * Permet d'indiquer qu'on ne veux pas utiliser de requête préparée.
     */
    public function no_prepare()
    {
        $this->prepareBool = false;
    }
    
    /**
     * Définie les options pour la requête préparée
     * @param array : Les options
     */
    public function set_prepare_option($option)
    {
        $this->prepare_option = $option;
    }
    
    /**
     * Permet d'ajouter une clause where à la requête
     * @param string : La condition du where
     * @return Sql_Select : L'instance de l'objet courant.
     */
    public function where($cond, $prepare=null)
    {
        $this->where[] = $cond;
        if(func_num_args() > 1 && is_array($prepare))
        {
            foreach($prepare as $key => $val)
            {
                if(isset($this->prepare[$key]) && $this->prepare[$key] != $val)
                {
                    new Exception('La clé '.$key.' pour la requête sql préparé est déjà utilisé avec une autre valeur.');
                }
                else
                {
                    $this->prepare[$key] = $val;
                }
            }
        }
        
        return $this;
    }
}
?>