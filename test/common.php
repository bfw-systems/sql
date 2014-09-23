<?php
/**
 * Actions à effectuer lors de l'initialisation du module par le framework.
 * @author Vermeulen Maxime <bulton.fr@gmail.com>
 * @package bfw-sql
 * @version 1.0
 */

//Disons que nous somme à l'origine du projet
//Je déclare une variable $rootPath à ici pour me simplifier mes inclusions.
$rootPath = realpath(__DIR__.'/../').'/';

$loader = require($rootPath.'vendor/autoload.php');
$loader->addPsr4('BFWSql\\', __DIR__.'/../src/classes/');
$loader->addPsr4('BFWSqlInterface\\', __DIR__.'/../src/interfaces/');
$loader->addPsr4('BFWSql\tests\units\\',  __DIR__.'/classes/');

//Instancie la classe Kernel
$BFWKernel = new BFW\Kernel;
$BFWKernel->set_debug(true);

error_reporting(E_ALL);
ini_set('display_errors', 'On');
ini_set('html_errors', true);

//J'inclus les fonctions du projet bfw-sql-alone
require_once($rootPath.'src/functions/functions.php');
require_once($rootPath.'vendor/bulton-fr/bfw/src/fonctions/global.php');

//Et sa config
require_once(__DIR__.'/config.php');

//$rootPath = __DIR__.'/../';
//require_once(__DIR__.'/../vendor/bulton-fr/bfw/src/BFW_init.php');

//Partie initialisation du projet bfw-sql-alone
if($bd_enabled) //La config déclare qu'une connexion sql doit être faite
{
    //On l'inclu et on stock l'instance de SqlConnect dans la variable déclaré
    $DB = new \BFWSql\SqlConnect($bd_host, $bd_user, $bd_pass, $bd_name, $bd_type, $bd_utf8);
    
    //Juste un petit au cas où pour pas que cette donnée ne traine dans le projet.
    unset($bd_pass);
}
else //Pas de connexion sql demandée
{
    $DB = null; //On déclare notre variable (initialisation) à null.
}
?>