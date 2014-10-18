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
     * Permet de vérifier si la requête finale a été assemblé et si ce n'est pas le cas de lancer l'assemblage.
     * 
     * @return void
     */
    public function is_Assembler();
    
    /**
     * Retourne la requête finale
     * 
     * @return string
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
     * 
     * @return void
     */
    public function query($req);
    
    /**
     * Permet d'indiquer qu'on ne veux pas utiliser de requête préparée.
     * 
     * @return void
     */
    public function no_prepare();
    
    /**
     * Définie les options pour la requête préparée
     * 
     * @param array $option Les options
     * 
     * @return void
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
     * @return \BFWSql\SqlActions L'instance de l'objet courant.
     */
    public function where($cond, $prepare=null);
    
    /**
     * Permet d'ajouter d'autres données à ajouter
     * 
     * @param array $champs Les données à ajouter : array('champSql' => 'données');
     * 
     * @return \BFWSql\SqlActions L'instance de l'objet courant.
     */
    public function addChamps($champs);
}
?>