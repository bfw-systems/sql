<?php
namespace BFWSqlInterface;

interface ISqlUpdate
{
    /**
     * Constructeur
     * @param Sql (référence) : L'instance Sql
     * @param string : La table sur laquelle agir
     * @param array : Les données à modifier : array('champSql' => 'données');
     */
    public function __construct(&$Sql, $table, $champs);
    
    /**
     * On assemble la requête
     */
    public function assembler_requete();
    
    /**
     * Permet de déclarer une requête UPDATE
     * @param string : La table sur laquelle agir
     * @param array : Les données à modifier : array('champSql' => 'données');
     * @return Sql_Update : L'instance de l'objet courant.
     */
    public function update($table, $champs);
    
    /**
     * Permet d'ajouter d'autres données à ajouter
     * @param array : Les données à ajouter : array('champSql' => 'données');
     * @return Sql_Update : L'instance de l'objet courant.
     */
    public function addChamps($champs);
}
?>