<?php
/**
 * Interface en rapport avec les classes de surcouche à pdo
 * 
 * @author Vermeulen Maxime <bulton.fr@gmail.com>
 * @version 1.0
 */

namespace BFWSqlInterface;

/**
 * Interface de la classe SqlActions
 * @package bfw-sql
 */
interface ISqlActions
{
    /**
     * Constructeur de la classe
     */
    public function __construct();
    
    /**
     * Permet de vérifier si la requête finale a été assemblé et si ce n'est pas le cas de lancer l'assemblage.
     */
    public function is_Assembler();
    
    /**
     * Retourne la requête finale
     */
    public function assemble();
    
    /**
     * Execute la requête (type INSERT, UPDATE et DELETE)
     * 
     * @throws \Exception Si la requête à echoué
     * 
     * @return \PDOStatement|bool : La ressource de la requête exécuté si elle a réussi, false sinon.
     */
    public function execute();
    
    /**
     * Permet d'inserer sa propre requête directement sans avoir à utiliser les méthodes from etc
     * 
     * @param string $req La requête
     */
    public function query($req);
    
    /**
     * Permet d'indiquer qu'on ne veux pas utiliser de requête préparée.
     */
    public function no_prepare();
    
    /**
     * Définie les options pour la requête préparée
     * 
     * @param array $option Les options
     */
    public function set_prepare_option($option);
    
    /**
     * Permet d'ajouter une clause where à la requête
     * 
     * @param string     $cond    La condition du where
     * @param arrya|null $prepare (default: null) Les infos pour la requête préparé
     * 
     * @throws \Exception : Si la clé utilisé sur la requête préparé est déjà utilisé.
     * 
     * @return Sql_Select L'instance de l'objet courant.
     */
    public function where($cond, $prepare=null);
}
?>