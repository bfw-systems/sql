<?php
/**
 * Interface en rapport avec les classes de surcouche à pdo
 * 
 * @author Vermeulen Maxime <bulton.fr@gmail.com>
 * @version 1.0
 */

namespace BFWSqlInterface;

/**
 * Interface de la classe Sql
 * @package bfw-sql
 */
interface ISql
{
    /**
     * Renvoi l'id du dernier élément ajouté en bdd
     * 
     * @param string|null $name (default: null) nom de la séquence pour l'id (pour PostgreSQL par exemple)
     * 
     * @return integer
     */
    public function der_id($name=null);
    
    /**
     * Renvoi l'id du dernier élément ajouté en bdd pour une table sans Auto Incrément
     * 
     * @param string       $table   La table
     * @param string       $champID Le nom du champ correspondant à l'id
     * @param string|array $order   Les champs sur lesquels se baser
     * @param string|array $where   Clause where
     * 
     * @return integer le dernier id, 0 si aucun résultat
     */
    public function der_id_noAI($table, $champID, $order, $where='');
    
    /**
     * Créer une instance de Sql_Select permettant de faire une requête de type SELECT
     * 
     * @param string $type (default: "array") Le type de retour pour les données. Valeurs possible : array|objet|object
     * 
     * @return \BFWSql\SqlSelect L'instance de l'objet Sql_Select créé
     */
    public function select($type='array');
    
    /**
     * Créer une instance de Sql_Insert permettant de faire une requête de type INSERT INTO
     * 
     * @param string $table  (default: null) La table sur laquelle agir
     * @param array  $champs (default: null) Les données à ajouter : array('champSql' => 'données');
     * 
     * @return \BFWSql\SqlInsert L'instance de l'objet Sql_Select créé
     */
    public function insert($table=null, $champs=null);
    
    /**
     * Créer une instance de Sql_Update permettant de faire une requête de type UPDATE
     * 
     * @param string $table  (default: null) La table sur laquelle agir
     * @param array  $champs (default: null) Les données à ajouter : array('champSql' => 'données');
     * 
     * @return \BFWSql\SqlUpdate L'instance de l'objet Sql_Select créé
     */
    public function update($table=null, $champs=null);
    
    /**
     * Créer une instance de Sql_Delete permettant de faire une requête de type DELETE FROM
     * 
     * @param string $table (default: null) La table sur laquelle agir
     * 
     * @return \BFWSql\SqlDelete L'instance de l'objet Sql_Select créé
     */
    public function delete($table=null);
    
    /**
     * Trouve le premier id libre pour une table et pour un champ
     * 
     * @param string $table La table
     * @param string $champ Le champ. Les valeurs du champ doivent être du type int.
     * 
     * @throws \Exception Si ue erreur dans la recherche d'id s'est produite
     * 
     * @return integer L'id libre trouvé. False si erreur
     */
    public function create_id($table, $champ);
    
    /**
     * Execute la requête mise en paramètre
     * 
     * @param string $requete La requête à exécuter
     * 
     * @throws \Exception Si la requête à echoué
     * 
     * @return \PDOStatement La ressource de la requête exécuté si elle a réussi.
     */
    public function query($requete);
    
    /**
     * Incrémente le nombre de requête effectué
     * 
     * @return void
     */
    public function upNbQuery();
    
    /**
     * Accesseur pour accéder au nombre de requête
     * 
     * @return integer Le nombre de requête
     */
    public function getNbQuery();
}
?>