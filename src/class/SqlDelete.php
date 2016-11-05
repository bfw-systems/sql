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
     * @param Sql         $Sql   (ref) L'instance Sql
     * @param string|null $table La table sur laquelle agir
     */
    public function __construct(Sql &$Sql, $table)
    {
        parent::__construct($Sql);
        
        //Par défault on prend le nom du modèle pour le nom de la table
        $this->table = $this->modeleName;
        
        //Si la table est déclaré, on prend sa valeur
        if($table != null) {$this->table = $table;}
    }
    
    /**
     * On assemble la requête
     * 
     * @return void
     */
    public function assembler_requete()
    {
        $lst_where = '';
        
        //On regarde s'il y a une clause where à mettre
        if(count($this->where) > 0)
        {
            $i = 0;
            $lst_where = ' WHERE ';
            
            //Chaque élément du tableau est une condition
            foreach($this->where as $val)
            {
                //Idem que la virgule, si une condition est déjà présente, on met un AND
                if($i > 0) {$lst_where .= ' AND ';}
                
                $lst_where .= $val;
                $i++;
            }
        }
        
        //Et on créer la requêtes
        $this->RequeteAssembler = 'DELETE FROM '.$this->prefix.$this->table.$lst_where;
        
        $this->callObserver();
    }
    
    /**
     * Permet de déclarer une requête DELETE
     * 
     * @param string $table La table sur laquelle agir
     * 
     * @return \BFWSql\SqlDelete L'instance de l'objet courant.
     */
    public function delete($table)
    {
        $this->table = $table;
        return $this;
    }
}
?>