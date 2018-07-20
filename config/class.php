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
    'RunnerMonolog'   => '\BfwSql\Runners\Monolog',
    'RunnerObservers' => '\BfwSql\Runners\Observers',
    'RunnerConnectDb' => '\BfwSql\Runners\ConnectDb',
    'CreatePdoDsn'    => '\BfwSql\CreatePdoDsn',
    'SqlConnect'      => '\BfwSql\SqlConnect',
    'ActionsDelete'   => '\BfwSql\Actions\Delete',
    'ActionsInsert'   => '\BfwSql\Actions\Insert',
    'ActionsSelect'   => '\BfwSql\Actions\Select',
    'ActionsUpdate'   => '\BfwSql\Actions\Update',
    'PDO'             => '\PDO'
];
