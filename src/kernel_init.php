<?php
/**
 * Actions à effectuer lors de l'initialisation du module par le framework.
 * @author Vermeulen Maxime <bulton.fr@gmail.com>
 * @package bfw-sql
 * @version 1.0
 */
 
require_once($rootPath.'modules/bfw-sql/functions/functions.php');
require_once($rootPath.'configs/bfw-sql/config.php');

if($bd_enabled)
{
    $DB = new \BFWSql\SqlConnect($bd_host, $bd_user, $bd_pass, $bd_name, $bd_type);
    unset($bd_pass);
    
    if($bd_observer)
    {
        $observerSql = new \BFWSql\SqlObserver;
        $Kernel->attachOther($observerSql);
    }
}
else
{
    $DB = null;
}
?>