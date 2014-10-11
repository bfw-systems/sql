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
     * @param array       $champs Les données à ajouter : array('champSql' => 'données');
     */
    public function __construct(Sql &$Sql, $table, $champs)
    {
        parent::__construct($Sql);
        
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
     * 
     * @param string $table  La table sur laquelle agir
     * @param array  $champs Les données à ajouter : array('champSql' => 'données');
     * 
     * @return SqlInsert L'instance de l'objet courant.
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
     * @return SqlInsert L'instance de l'objet courant.
     */
    public function data($champs)
    {
        foreach($champs as $column => $data)
        {
            if(isset($this->champs[$column]) && $this->champs[$column] != $data)
            {
                throw new \Exception('Une valeur pour la colonne '.$column.' est déjà déclaré.');
            }
            else
            {
                $this->champs[$column] = $data;
            }
        }
        
        return $this;
    }
} 
?>