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
$loaderAddPsr4 = 'addPsr4';

$loader->addPsr4('BFWSql\\', __DIR__.'/../src/classes/');
$loader->addPsr4('BFWSqlInterface\\', __DIR__.'/../src/interfaces/');
$loader->addPsr4('BFWSql\tests\units\\',  __DIR__.'/classes/');

$forceConfig = true;
require_once(__DIR__.'/../vendor/bulton-fr/bfw/install/skeleton/config.php');
$base_url = 'http://test.bulton.fr/bfw-v2/';

require_once(__DIR__.'/../vendor/bulton-fr/bfw/src/BFW_init.php');

error_reporting(E_ALL);
ini_set('display_errors', 'On');
ini_set('html_errors', true);

//J'inclus les fonctions du projet bfw-sql-alone
require_once($rootPath.'src/functions/functions.php');

//Et sa config
require_once(__DIR__.'/config.php');

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