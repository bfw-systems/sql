<?php
namespace BFWSqlInterface;

interface ISql
{
    /**
     * Modifie le nom de la table sur laquelle on travail
     * @param string $name : le nom de la table
     */
    public function set_modeleName($name);
    
    /**
     * Renvoi l'id du dernier élément ajouté en bdd
     * @param string nom de la séquence pour l'id (PostgreSQL)
     * @return int : l'id
     */
    public function der_id($name=NULL);
    
    /**
     * Renvoi l'id du dernier élément ajouté en bdd pour une table sans Auto Incrément
     * @param string : La table
     * @param string : Le nom du champ correspondant à l'id
     * @param strng/array : Les champs sur lesquels se baser
     * @param strng/array : Clause where
     * @return int : l'id
     */
    public function der_id_noAI($table, $champID, $order, $where='');
    
    /**
     * Créer une instance de Sql_Select permettant de faire une requête de type SELECT
     * @param string (array|objet|object) : Le type de retour qui sera à faire pour les données. Par tableau en tableau.
     * @return Sql_Select : L'instance de l'objet Sql_Select créé
     */
    public function select($type='array');
    
    /**
     * Créer une instance de Sql_Insert permettant de faire une requête de type INSERT INTO
     * @return Sql_Insert : L'instance de l'objet Sql_Select créé
     * @param string [opt] : La table sur laquelle agir
     * @param array [opt] : Les données à ajouter : array('champSql' => 'données');
     */
    public function insert($table=null, $champs=null);
    
    /**
     * Créer une instance de Sql_Update permettant de faire une requête de type UPDATE
     * @return Sql_Update : L'instance de l'objet Sql_Select créé
     * @param string [opt] : La table sur laquelle agir
     * @param array [opt] : Les données à modifier : array('champSql' => 'données');
     */
    public function update($table=null, $champs=null);
    
    /**
     * Créer une instance de Sql_Delete permettant de faire une requête de type DELETE FROM
     * @return Sql_Delete : L'instance de l'objet Sql_Select créé
     * @param string [opt] : La table sur laquelle agir
     */
    public function delete($table=null);
    
    /**
     * Trouve le premier id libre pour une table et pour un champ
     * @param string : La table
     * @param string : Le champ
     * @return int/bool : L'id libre trouvé. False si erreur
     */
    public function create_id($table, $champ);
    
    /**
     * Execute la requête mise en paramètre
     * Génère une exception s'il y a eu un échec
     * @param string : La requête à exécuter
     * @return mixed : La ressource de la requête exécuté si elle a réussi, false sinon.
     */
    public function query($requete);
}
?>