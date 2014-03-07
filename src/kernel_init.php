<?php
require_once($path.'modules/bfw-sql/config.php');

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