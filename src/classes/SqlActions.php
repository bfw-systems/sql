<?php
/**
 * Classes en rapport avec les sgdb
 * @author Vermeulen Maxime <bulton.fr@gmail.com>
 * @version 1.0
 */

namespace BFWSql;

use \Exception;

/**
 * Classe parent aux classes Sql_Select, Sql_Insert, Sql_Update et Sql_Delete
 * Elle stock l'instance de pdo et définie quelques méthodes.
 * @package bfw-sql
 */
abstract class SqlActions implements \BFWSqlInterface\ISqlActions
{
    /**
     * @var $_kernel L'instance du Kernel
     */
    protected $_kernel;
    
    /**
     * @var $_sql L'instance de l'objet Sql
     */
    protected $_sql;
    
    /**
     * @var $PDO L'instance de pdo
     */
    protected $PDO;
    
    /**
     * @var $RequeteAssembler La requête final qui sera exécutée
     */
    protected $RequeteAssembler = '';
    
    /**
     * @var $modeleName Le nom de la table du modele
     */
    protected $modeleName = null;
    
    /**
     * @var $prefix Le préfix des tables
     */
    protected $prefix;
    
    /**
     * @var $prepare Permet de savoir si on utilise les requêtes préparées ou non
     */
    protected $prepareBool = true;
    
    /**
     * @var $table La table sur laquel agir
     */
    protected $table = '';
    
    /**
     * @var $champs Les données à insérer
     */
    protected $champs = array();
    
    /**
     * @var $where Les clauses where
     */
    protected $where = array();
    
    /**
     * @var $prepare Les arguments de la requête préparée
     */
    protected $prepare = array();
    
    /**
     * @var $prepare_option Les options pour la requête préparée
     */
    protected $prepare_option = array();
    
    /**
     * @var bool $no_result Permet de savoir si l'echec est du Ã  la requÃªte qui n'a rien renvoyÃ© ou une erreur
     */
    protected $no_result = false;
    
    /**
     * Constructeur de la classe
     * 
     * @param Sql $Sql : (ref) Instance de la classe Sql
     */
    public function __construct(Sql &$Sql)
    {
        $this->_sql = $Sql;
        $this->PDO  = $this->_sql->getPDO();
        
        $this->_kernel = getKernel();
        $this->_kernel->set_observers($this->_kernel->get_observers());
        
        $this->prefix = $Sql->getPrefix();;
        $this->modeleName = $Sql->getModeleName();
    }
    
    /**
     * Permet de vérifier si la requête finale a été assemblé et si ce n'est pas le cas de lancer l'assemblage.
     * 
     * @return void
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
     * 
     * @return string
     */
    public function assemble()
    {
        $this->is_Assembler();
        return $this->RequeteAssembler;
    }
    
    /**
     * Execute la requête (type INSERT, UPDATE et DELETE)
     * 
     * @throws \Exception Si la requête à echoué
     * 
     * @return \PDOStatement|integer|bool : La ressource de la requête exécuté si elle a réussi, false sinon.
     */
    public function execute()
    {
        $this->_sql->upNbQuery();
        $this->is_Assembler(); //On vérifie que la requête est bien généré
        
        //Gestion si c'est une requête préparé
        if($this->prepareBool)
        {
            //Prépare et exécute la requête.
            $req = $this->PDO->prepare($this->RequeteAssembler, $this->prepare_option);
            $req->execute($this->prepare);
            $erreur = $req->errorInfo();
        }
        else //Requête "normal"
        {
            //On exécute la requête
            $methodExecuted = 'exec';
            if(get_class($this) == '\BFWSql\SqlSelect') {$methodExecuted = 'query';}
            
            $req = $this->PDO->{$methodExecuted}($this->RequeteAssembler);
            
            //Récupération d'une éventuelle erreur
            $erreur = $this->PDO->errorInfo();
        }
        $this->req = $req;
        
        //S'il y a une erreur, on génère une exception.
        if($erreur[0] != null && $erreur[0] != '00000')
        {
            throw new \Exception($erreur[2]);
        }
        else
        {
            //Si la requête à réussi, on retourne sa ressource
            if($req)
            {
                //On vérifie le nombre de résultat. S'il y a des résultat, on retourne la requête
                if($this->nb_result() > 0) {return $req;}
                else {$this->no_result = true;} //Si pas de résultat, on ne note
            }
        }
        
        //Retourne false si fail ou si pas de résultat.
        return false;
    }
    
    /**
     * Retourne le nombre de ligne retourner par la requête
     * 
     * @return int|bool le nombre de ligne. false si ça a échoué.
     */
    public function nb_result()
    {
        //Si la requête n'a pas fail, on retourne directement le nombre de ligne
            if($this->req != false && is_object($this->req)) {return $this->req->rowCount();}
        elseif(is_integer($this->req)) {return $this->req;} //Si pdo->exec()
        else {return false;} //Fail : retourne false.
    }
    
    /**
     * Permet d'inserer sa propre requête directement sans avoir à utiliser les méthodes from etc
     * 
     * @param string $req La requête
     * 
     * @return void
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
     * 
     * @param array $option Les options
     * 
     * @return void
     */
    public function set_prepare_option($option)
    {
        $this->prepare_option = $option;
    }
    
    /**
     * Permet d'ajouter une clause where à la requête
     * 
     * @param string     $cond    La condition du where
     * @param arrya|null $prepare (default: null) Les infos pour la requête préparé
     * 
     * @throws \Exception : Si la clé utilisé sur la requête préparé est déjà utilisé.
     * 
     * @return \BFWSql\SqlActions L'instance de l'objet courant.
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
                    throw new \Exception('La clé '.$key.' pour la requête sql préparé est déjà utilisé avec une autre valeur.');
                }
                else
                {
                    $this->prepare[$key] = $val;
                }
            }
        }
        
        return $this;
    }
    
    /**
     * Permet de générer une clause where dans les requêtes
     * 
     * @return string : La clause where finale
     */
    protected function generateWhere()
    {
        $where = '';
        if(count($this->where) > 0) //S'il y a une partie where à faire
        {
            $where = ' WHERE ';
            //Boucle sur les conditions
            foreach($this->where as $val)
            {
                //S'il y a déjà une condition, on rajoute le AND
                if($where != ' WHERE ') {$where .= ' AND ';}
                $where .= $val;
            } 
        }
        
        return $where;
    }
    
    /**
     * Permet d'ajouter d'autres données à ajouter
     * 
     * @param array $champs Les données à ajouter : array('champSql' => 'données');
     * 
     * @return \BFWSql\SqlActions L'instance de l'objet courant.
     */
    public function addChamps($champs)
    {
        //Pour chaque champs
        foreach($champs as $column => $data)
        {
            //Vérifie que le champ n'est pas déjà modifié
            if(isset($this->champs[$column]) && $this->champs[$column] != $data)
            {
                throw new \Exception('Une valeur pour la colonne '.$column.' est déjà déclaré.');
            }
            
            //On ajoute à la liste des champs à modifier.
            $this->champs[$column] = $data;
        }
        
        return $this;
    }
}
?>