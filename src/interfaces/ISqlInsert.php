<?php
namespace BFWSqlInterface;

interface ISqlInsert
{
    /**
     * Constructeur
     * @param Sql (référence) : L'instance Sql
     * @param string : La table sur laquelle agir
     * @param array : Les données à ajouter : array('champSql' => 'données');
     */
    public function __construct(&$Sql, $table, $champs);
    
    /**
     * On assemble la requête
     */
    public function assembler_requete();
    
    /**
     * Permet de déclarer une requête INSERT INTO
     * @param string : La table sur laquelle agir
     * @param array : Les données à ajouter : array('champSql' => 'données');
     * @return Sql_Insert : L'instance de l'objet courant.
     */
    public function insert($table, $champs);
    
    /**
     * Permet d'ajouter d'autres données à ajouter
     * @param array : Les données à ajouter : array('champSql' => 'données');
     * @return Sql_Insert : L'instance de l'objet courant.
     */
    public function data($champs);
}
?>