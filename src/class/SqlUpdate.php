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
     * @param Sql         $Sql    (ref) L'instance Sql
     * @param string|null $table  La table sur laquelle agir
     * @param array       $champs (default: null) Les données à modifier : array('champSql' => 'données');
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
        //Si des champs à modifier sont indiqués
        if(count($this->champs) > 0)
        {
            //Initialisation
            $lst_champ = $lst_where = '';
            $i = 0;
            
            //On liste les données à modifier
            foreach($this->champs as $key => $val)
            {
                //S'il y a déjà un champ, on met une , entre chacun
                if($i > 0) {$lst_champ .= ', ';}
                
                $lst_champ .= $key.'='.$val;
                $i++;
            }
            
            //On regarde s'il y a une clause where à mettre
            if(count($this->where) > 0)
            {
                $lst_where = ' WHERE ';
                $i = 0;
                
                //Chaque élément du tableau est une condition
                foreach($this->where as $val)
                {
                    //Idem que la virgule, si une condition est déjà présente, on met un AND
                    if($i > 0) {$lst_where .= ' AND ';}
                    
                    $lst_where .= $val;
                    $i++;
                }
            }
            
            //Et on créer la requête
            $this->RequeteAssembler = 'UPDATE '.$this->prefix.$this->table.' SET '.$lst_champ.$lst_where;
            
            $this->callObserver();
        }
    }
    
    /**
     * Permet de déclarer une requête UPDATE
     * 
     * @param string $table  La table sur laquelle agir
     * @param array  $champs Les données à modifier : array('champSql' => 'données');
     * 
     * @return \BFWSql\SqlUpdate L'instance de l'objet courant.
     */
    public function update($table, $champs)
    {
        $this->table = $table;
        $this->champs = $champs;
        
        return $this;
    }
}
?>