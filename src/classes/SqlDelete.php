<?php
/**
 * Classes en rapport avec les sgdb
 * @author Vermeulen Maxime <bulton.fr@gmail.com>
 * @version 1.0
 */

namespace BFWSql;

/**
 * Classe gérant les requêtes de type DELETE FROM
 * @package bfw-sql
 */
class SqlDelete extends SqlActions implements \BFWSqlInterface\ISqlDelete
{
    /**
     * Constructeur
     * 
     * @param Sql    $Sql   (ref) L'instance Sql
     * @param string $table La table sur laquelle agir
     */
    public function __construct(Sql &$Sql, $table)
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
    }
    
    /**
     * On assemble la requête
     */
    public function assembler_requete()
    {
        $lst_where = '';
        
        if(count($this->where) > 0) //On regarde s'il y a une clause where à mettre
        {
            $i = 0;
            $lst_where = ' WHERE ';
            
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
        
        //Et on créer la requêtes
        $this->RequeteAssembler = 'DELETE FROM '.$this->prefix.$this->table.$lst_where;
    }
    
    /**
     * Permet de déclarer une requête DELETE
     * 
     * @param string $table La table sur laquelle agir
     * 
     * @return Sql_Delete L'instance de l'objet courant.
     */
    public function delete($table)
    {
        $this->table = $table;
        return $this;
    }
}
?>