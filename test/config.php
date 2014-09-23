<?php
/**
 * Fichier de configuration du module bfw-sql
 * @author Vermeulen Maxime <bulton.fr@gmail.com>
 * @package bfw-sql
 * @version 1.0
 */

//*** Base De Données ***
$bd_host    = 'localhost'; //Le serveur où est la base (ex: localhost)
$bd_name    = 'bfw_sql_alone_test'; //Nom de la Base de donnée
$bd_user    = 'travis'; //Le nom d'utilisateur
$bd_pass    = ''; //Le mot de passe
$bd_type    = 'mysql'; //Le type de bdd (ex: mysql) (ex: pgsql)
$bd_prefix  = ''; //Préfix en début de table
$bd_utf8    = true; //Si la db est en utf-8 ou non.
$bd_debug   = false; //Si on veux être en mode debug ou non.
$bd_enabled = true; //Active ou non la connexion sql
//*** Base De Données ***/
