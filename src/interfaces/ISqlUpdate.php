<?php
/**
 * Interface en rapport avec les classes de surcouche à pdo
 * 
 * @author Vermeulen Maxime <bulton.fr@gmail.com>
 * @version 1.0
 */

namespace BFWSqlInterface;

/**
 * Interface de la classe SqlUpdate
 * @package bfw-sql
 */
interface ISqlUpdate
{
    /**
     * On assemble la requête
     */
    public function assembler_requete();
    
    /**
     * Permet de déclarer une requête UPDATE
     * 
     * @param string $table  La table sur laquelle agir
     * @param array  $champs Les données à modifier : array('champSql' => 'données');
     * 
     * @return Sql_Update L'instance de l'objet courant.
     */
    public function update($table, $champs);
    
    /**
     * Permet d'ajouter d'autres données à ajouter
     * 
     * @param array $champs Les données à ajouter : array('champSql' => 'données');
     * 
     * @return Sql_Update L'instance de l'objet courant.
     */
    public function addChamps($champs);
}
?>