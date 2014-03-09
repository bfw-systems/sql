<?php
/**
 * Classes en rapport avec les sgdb
 * @author Vermeulen Maxime <bulton.fr@gmail.com>
 * @version 1.0
 */

namespace BFWSql;

/**
 * Classe gérant les requêtes de type UPDATE
 * @package bfw-sql
 */
class SqlUpdate extends SqlActions implements \BFWSqlInterface\ISqlUpdate
{
    /**
     * Constructeur
     * 
     * @param Sql    $Sql    (ref) L'instance Sql
     * @param string $table  La table sur laquelle agir
     * @param array  $champs Les données à modifier : array('champSql' => 'données');
     */
    public function __construct(Sql &$Sql, $table, $champs)
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
        if(count($this->champs) > 0)
        {
            $lst_champ = $lst_where = '';
            $i = 0;
            
            foreach($this->champs as $key => $val) //On liste les données à modifier
            {
                if($i > 0)
                {
                    $lst_champ .= ', ';
                }
                
                $lst_champ .= $key.'='.$val;
                $i++;
            }
            
            if(count($this->where) > 0) //On regarde s'il y a une clause where à mettre
            {
                $lst_where = ' WHERE ';
                $i = 0;
                
                foreach($this->where as $val) //Chaque élément du tableau est une condition
                {
                    if($i > 0)
                    {
                        $lst_where .= ' AND ';
                    }
                    
                    $lst_where .= $val;
                    $i++;
                }
            }
            
            //Et on créer la requête
            $this->RequeteAssembler = 'UPDATE '.$this->prefix.$this->table.' SET '.$lst_champ.$lst_where;
        }
    }
    
    /**
     * Permet de déclarer une requête UPDATE
     * 
     * @param string $table  La table sur laquelle agir
     * @param array  $champs Les données à modifier : array('champSql' => 'données');
     * 
     * @return Sql_Update L'instance de l'objet courant.
     */
    public function update($table, $champs)
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
     * @return Sql_Update L'instance de l'objet courant.
     */
    public function addChamps($champs)
    {
        $this->champs[] = $champs;
        return $this;
    }
}
?>