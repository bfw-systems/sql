<?php
/**
 * Interface en rapport avec les classes de surcouche à pdo
 * 
 * @author Vermeulen Maxime <bulton.fr@gmail.com>
 * @version 1.0
 */

namespace BFWSqlInterface;

/**
 * Interface de la classe SqlInsert
 * @package bfw-sql
 */
interface ISqlInsert
{
    /**
     * On assemble la requête
     * @return void
     */
    public function assembler_requete();
    
    /**
     * Permet de déclarer une requête INSERT INTO
     * 
     * @param string $table  La table sur laquelle agir
     * @param array  $champs Les données à ajouter : array('champSql' => 'données');
     * 
     * @return \BFWSql\SqlInsert L'instance de l'objet courant.
     */
    public function insert($table, $champs);
    
    /**
     * Permet d'ajouter d'autres données à ajouter
     * 
     * @param array $champs Les données à ajouter : array('champSql' => 'données');
     * 
     * @return \BFWSql\SqlInsert L'instance de l'objet courant.
     */
    public function data($champs);
}
?>