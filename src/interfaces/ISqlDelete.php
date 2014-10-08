<?php
/**
 * Interface en rapport avec les classes de surcouche à pdo
 * 
 * @author Vermeulen Maxime <bulton.fr@gmail.com>
 * @version 1.0
 */

namespace BFWSqlInterface;

/**
 * Interface de la classe SqlDelete
 * @package bfw-sql
 */
interface ISqlDelete
{
    /**
     * On assemble la requête
     * @return void
     */
    public function assembler_requete();
    
    /**
     * Permet de déclarer une requête DELETE
     * 
     * @param string $table La table sur laquelle agir
     * 
     * @return \BFWSql\SqlDelete L'instance de l'objet courant.
     */
    public function delete($table);
}
?>