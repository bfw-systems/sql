<?php
namespace BFWSqlInterface;

interface ISqlActions
{
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
     * @return mixed : La ressource de la requête exécuté si elle a réussi, false sinon (avec une Exception).
     */
    public function execute();
    
    /**
     * Permet d'inserer sa propre requête directement sans avoir à utiliser les méthodes from etc
     * @param string : La requête
     */
    public function query($req);
    
    /**
     * Permet d'indiquer qu'on ne veux pas utiliser de requête préparée.
     */
    public function no_prepare();
    
    /**
     * Définie les options pour la requête préparée
     * @param array : Les options
     */
    public function set_prepare_option($option);
    
    /**
     * Permet d'ajouter une clause where à la requête
     * @param string : La condition du where
     * @return Sql_Select : L'instance de l'objet courant.
     */
    public function where($cond, $prepare=null);
}
?>