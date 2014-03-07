<?php
namespace BFWSqlInterface;

interface ISqlDelete
{
    /**
     * Constructeur
     * @param Sql (référence) : L'instance Sql
     * @param string : La table sur laquelle agir
     */
    public function __construct(&$Sql, $table);
    
    /**
     * On assemble la requête
     */
    public function assembler_requete();
    
    /**
     * Permet de déclarer une requête DELETE
     * @param string : La table sur laquelle agir
     * @return Sql_Delete : L'instance de l'objet courant.
     */
    public function delete($table);
}
?>