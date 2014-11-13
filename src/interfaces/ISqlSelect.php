<?php
/**
 * Interface en rapport avec les classes de surcouche à pdo
 * 
 * @author Vermeulen Maxime <bulton.fr@gmail.com>
 * @version 1.0
 */

namespace BFWSqlInterface;

/**
 * Interface de la classe SqlSelect
 * @package bfw-sql
 */
interface ISqlSelect
{
    /**
     * On assemble la requête
     * 
     * @return void
     */
    public function assembler_requete();
    
    /**
     * Permet de récupérer les informations à propos de la table sur laquel on souhaite agir.
     * 
     * @param mixed $table Les infos sur la table
     * 
     * @return array les infos découpé ['tableName'] contient le nom de la table et ['as'] sont raccourcis. 
     * Si as n'a pas été indiqué, il vaux la valeur de tableName
     */
    public function infosTable($table);
    
    /**
     * Permet d'indiquer les infos pour le FROM
     * 
     * @param string|array $table  La table du FROM, si tableau, la clé est la valeur du AS
     * @param string|array $champs (default: "*") Le ou les champs à récupérer de cette table
     * 
     * @return \BFWSql\SqlSelect L'instance de l'objet courant.
     */
    public function from($table, $champs='*');
    
    /**
     * Permet d'indiquer les infos pour le FROM
     * 
     * @param SqlActions $req L'instance de la class SqlActions ou qui l'étends correspondant à la sous-requête
     * @param string     $as  La valeur du AS pour la sous-requête
     * 
     * @return \BFWSql\SqlSelect L'instance de l'objet courant.
     */
    public function subQuery($req, $as);
    
    /**
     * Permet d'indiquer les infos pour la jointure
     * 
     * @param string|array $table  La table du JOIN, si tableau, la clé est la valeur du AS
     * @param string       $on     La valeur de la partie ON de la jointure
     * @param string|array $champs (default: "*") Le ou les champs à récupérer de cette table
     * 
     * @return \BFWSql\SqlSelect L'instance de l'objet courant.
     */
    public function join($table, $on, $champs='*');
    
    /**
     * Permet d'indiquer les infos pour la jointure
     * 
     * @param string|array $table  La table du LEFT JOIN, si tableau, la clé est la valeur du AS
     * @param string       $on     La valeur de la partie ON de la jointure
     * @param string|array $champs (default: "*") Le ou les champs à récupérer de cette table
     * 
     * @return \BFWSql\SqlSelect L'instance de l'objet courant.
     */
    public function joinLeft($table, $on, $champs='*');
    
    /**
     * Permet d'indiquer les infos pour la jointure
     * 
     * @param string|array $table  La table du RIGHT JOIN, si tableau, la clé est la valeur du AS
     * @param string       $on     La valeur de la partie ON de la jointure
     * @param string|array $champs (default: "*") Le ou les champs à récupérer de cette table
     * 
     * @return \BFWSql\SqlSelect L'instance de l'objet courant.
     */
    public function joinRight($table, $on, $champs='*');
    
    /**
     * Permet d'ajouter une clause order by à la requête
     * 
     * @param string $cond Le champ concerné par l'order by
     * 
     * @return \BFWSql\SqlSelect L'instance de l'objet courant.
     */
    public function order($cond);
    
    /**
     * Permet d'ajouter une clause limit à la requête
     * 
     * @param array|string $limit Soit 1 paramètre (le nombre à retourner), soit 2 paramètres (le nombre où on commence et le nombre à retourner)
     * 
     * @return \BFWSql\SqlSelect L'instance de l'objet courant.
     */
    public function limit($limit);
    
    /**
     * Permet d'ajouter une clause group by à la requête
     * 
     * @param string $cond Le champ concerné par le group by
     * 
     * @return \BFWSql\SqlSelect L'instance de l'objet courant.
     */
    public function group($cond);
    
    /**
     * Retourne une seule ligne de résultat
     * 
     * @return mixed Les données sous la forme demandé, false s'il y a un problème avec la requête (l'erreur est automatiquement affiché).
     */
    public function fetchRow();
    
    /**
     * Retourne toutes les lignes de la requête
     * 
     * @return mixed Les données sous la forme demandé mis dans un array où chaque noeud est une ligne
     * false s'il y a un problème avec la requête (l'erreur est automatiquement affiché).
     */
    public function fetchAll();
    
    /**
     * Retourne le nombre de ligne retourner par la requête
     * 
     * @return int|bool le nombre de ligne. false si ça a échoué.
     */
    public function nb_result();
}
?>