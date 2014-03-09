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
     * Constructeur
     * 
     * @param Sql    $Sql    (ref) L'instance Sql
     * @param string $table  La table sur laquelle agir
     * @param array  $champs Les données à ajouter : array('champSql' => 'données');
     */
    public function __construct(Sql &$Sql, $table, $champs);
    
    /**
     * On assemble la requête
     */
    public function assembler_requete();
    
    /**
     * Permet de déclarer une requête INSERT INTO
     * 
     * @param string $table  La table sur laquelle agir
     * @param array  $champs Les données à ajouter : array('champSql' => 'données');
     * 
     * @return Sql_Insert L'instance de l'objet courant.
     */
    public function insert($table, $champs);
    
    /**
     * Permet d'ajouter d'autres données à ajouter
     * 
     * @param array $champs Les données à ajouter : array('champSql' => 'données');
     * 
     * @return Sql_Insert L'instance de l'objet courant.
     */
    public function data($champs);
}
?>