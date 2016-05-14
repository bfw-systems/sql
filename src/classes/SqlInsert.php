<?php
/**
 * Classes en rapport avec les sgdb
 * @author Vermeulen Maxime <bulton.fr@gmail.com>
 * @version 1.0
 */

namespace BFWSql;

/**
 * Classe gérant les requêtes de type INSERT INTO
 * @package bfw-sql
 */
class SqlInsert extends SqlActions implements \BFWSqlInterface\ISqlInsert
{
    /**
     * Constructeur
     * 
     * @param Sql         $Sql    (ref) L'instance Sql
     * @param string|null $table  La table sur laquelle agir
     * @param array       $champs (default: null) Les données à ajouter : array('champSql' => 'données');
     */
    public function __construct(Sql &$Sql, $table, $champs)
    {
        parent::__construct($Sql);
        
        //Par défault on prend le nom du modèle pour le nom de la table
        $this->table = $this->modeleName;
        
        //Si la table est déclaré, on prend sa valeur
        if($table != null) {$this->table = $table;}
        
        //Si des champs à modifier sont déjà indiqué, on initialise avec
        if($champs != null) {$this->champs = $champs;}
    }
    
    /**
     * On assemble la requête
     * 
     * @return void
     */
    public function assembler_requete()
    {
        //Initialisation
        $lst_champ = $lst_val = '';
        $i = 0;
        
        //Pour chaque donnée on sépare le champ et sa valeur pour la requête
        foreach($this->champs as $champ => $val)
        {
            //S'il y a déjà un champ, on met une , entre chacun
            if($i > 0)
            {
                $lst_champ .= ',';
                $lst_val .= ',';
            }
            
            $lst_champ .= '`'.$champ.'`';
            $lst_val .= '\''.$val.'\'';
            $i++;
        }
        
        if(!($lst_champ === '' || $lst_val === ''))
        {
            //Et on créer la requête
            $this->RequeteAssembler = 'INSERT INTO '.$this->prefix.$this->table.' ('.$lst_champ.') VALUES ('.$lst_val.')';
        }
        
        $this->callObserver();
    }
    
    /**
     * Permet de déclarer une requête INSERT INTO
     * 
     * @param string $table  La table sur laquelle agir
     * @param array  $champs Les données à ajouter : array('champSql' => 'données');
     * 
     * @return \BFWSql\SqlInsert L'instance de l'objet courant.
     */
    public function insert($table, $champs)
    {
        $this->table = $table;
        $this->champs = $champs;
        
        return $this;
    }
    
    /**
     * Permet d'ajouter d'autres données à ajouter
     * 
     * @param array $champs Les données à ajouter : array('champSql' => 'données');
     * 
     * @throws \Exception : Erreur si colonne déjà utilisé
     * 
     * @return \BFWSql\SqlInsert L'instance de l'objet courant.
     */
    public function data($champs)
    {
        return $this->addChamps($champs);
    }
} 
?>