<?php
/**
 * Interface en rapport avec les classes de surcouche à pdo
 * 
 * @author Vermeulen Maxime <bulton.fr@gmail.com>
 * @version 1.0
 */

namespace BFWSqlInterface;

/**
 * Interface de la classe SqlConnect
 * @package bfw-sql
 */
interface ISqlConnect
{
    /**
     * Permet de protéger une requête contre les injections et autres.
     * 
     * @param string $string la requêtre sql
     * 
     * @return string la requête sql protégé
     */
    public function protect($string);
    
    /**
     * Accesseur pour accéder à $this->nb_query
     * 
     * @return integer Le nombre de requête
     */
    public function getNbQuery();
    
    /**
     * Incrémente le nombre de requête effectué
     * 
     * @return void
     */
    public function upNbQuery();
}
?>