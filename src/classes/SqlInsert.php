<?php
/**
 * Classes en rapport avec les sgdb
 * @author Vermeulen Maxime
 * @version 1.0
 */

namespace BFWSql;

/**
 * Classe gérant les requêtes de type INSERT INTO
 * 
 * @author Vermeulen Maxime
 * @package BFW
 * @version 1.0
 */
class SqlInsert extends SqlActions implements \BFWSqlInterface\ISqlInsert
{
    /**
     * Constructeur
     * @param Sql (référence) : L'instance Sql
     * @param string : La table sur laquelle agir
     * @param array : Les données à ajouter : array('champSql' => 'données');
     */
    public function __construct(&$Sql, $table, $champs)
    {
        parent::__construct();
        
        $this->PDO = &$Sql->PDO;
        $this->prefix = $Sql->prefix;
        $this->modeleName = $Sql->modeleName;
        
        if($table != null)
        {
            $this->table = $table;
        }
        else
        {
            $this->table = $this->modeleName;
        }
        
        if($champs != null)
        {
            $this->champs = $champs;
        }
    }
    
    /**
     * On assemble la requête
     */
    public function assembler_requete()
    {
        if(count($this->champs) > 0) //On vérifie qu'il y a bien des données à insérer
        {
            $lst_champ = $lst_val = '';
            $i = 0;
            foreach($this->champs as $champ => $val) //Pour chaque donnée on sépare le champ et sa valeur pour la requête
            {
                if($i > 0)
                {
                    $lst_champ .= ',';
                    $lst_val .= ',';
                }
                
                $lst_champ .= '`'.$champ.'`';
                $lst_val .= '\''.$val.'\'';
                $i++;
            }
            
            //Et on créer la requête
            $this->RequeteAssembler = 'INSERT INTO '.$this->prefix.$this->table.' ('.$lst_champ.') VALUES ('.$lst_val.')';
        }
    }
    
    /**
     * Permet de déclarer une requête INSERT INTO
     * @param string : La table sur laquelle agir
     * @param array : Les données à ajouter : array('champSql' => 'données');
     * @return Sql_Insert : L'instance de l'objet courant.
     */
    public function insert($table, $champs)
    {
        $this->table = $table;
        $this->champs = $champs;
        
        return $this;
    }
    
    /**
     * Permet d'ajouter d'autres données à ajouter
     * @param array : Les données à ajouter : array('champSql' => 'données');
     * @return Sql_Insert : L'instance de l'objet courant.
     */
    public function data($champs)
    {
        $this->champs[] = $champs;
        return $this;
    }
} 
?>