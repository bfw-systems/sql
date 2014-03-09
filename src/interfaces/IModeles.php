<?php
/**
 * Interface en rapport avec les modèles
 * 
 * @author Vermeulen Maxime <bulton.fr@gmail.com>
 * @version 1.0
 */

namespace BFWSqlInterface;

/**
 * Interface de la classe Modeles
 * @package bfw-sql
 */
interface IModeles
{
    /**
     * Consntructeur: Récupère la connexion Sql_connect
     */
    public function __construct();
}
?>