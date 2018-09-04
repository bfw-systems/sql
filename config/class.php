<?php
/**
 * Config file for bfw-sql module
 * 
 * @author Vermeulen Maxime <bulton.fr@gmail.com>
 * @package bfw-sql
 * @version 2.0
 */

/**
 * Define class to use.
 * 
 * Can not define because extends for :
 * * Sql
 * * SqlActions
 * Modele is extended by user.
 */
return [
    'PDO'             => '\PDO',
    'CreatePdoDsn'    => '\BfwSql\CreatePdoDsn',
    'SqlConnect'      => '\BfwSql\SqlConnect',
    
    'ExecutersCommon' => '\BfwSql\Executers\Common',
    'ExecutersSelect' => '\BfwSql\Executers\Select',
    
    'QueriesDelete'   => '\BfwSql\Queries\Delete',
    'QueriesInsert'   => '\BfwSql\Queries\Insert',
    'QueriesSelect'   => '\BfwSql\Queries\Select',
    'QueriesUpdate'   => '\BfwSql\Queries\Update',
    
    'RunnerMonolog'   => '\BfwSql\Runners\Monolog',
    'RunnerObservers' => '\BfwSql\Runners\Observers',
    'RunnerConnectDB' => '\BfwSql\Runners\ConnectDB',
];
