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
    'PDO'                 => '\PDO',
    'CreatePdoDsn'        => '\BfwSql\CreatePdoDsn',
    'SqlConnect'          => '\BfwSql\SqlConnect',
    
    'ExecutersCommon'     => '\BfwSql\Executers\Common',
    'ExecutersSelect'     => '\BfwSql\Executers\Select',
    
    'QueriesDelete'       => '\BfwSql\Queries\Delete',
    'QueriesInsert'       => '\BfwSql\Queries\Insert',
    'QueriesSelect'       => '\BfwSql\Queries\Select',
    'QueriesUpdate'       => '\BfwSql\Queries\Update',
    
    'QueriesSgbdCubrid'   => '\BfwSql\Queries\SGBD\Cubrid',
    'QueriesSgbdDblib'    => '\BfwSql\Queries\SGBD\Dblib',
    'QueriesSgbdFirebird' => '\BfwSql\Queries\SGBD\Firebird',
    'QueriesSgbdIbe'      => '\BfwSql\Queries\SGBD\Ibe',
    'QueriesSgbdInformix' => '\BfwSql\Queries\SGBD\Informix',
    'QueriesSgbdMysql'    => '\BfwSql\Queries\SGBD\Mysql',
    'QueriesSgbdOci'      => '\BfwSql\Queries\SGBD\Oci',
    'QueriesSgbdOdbc'     => '\BfwSql\Queries\SGBD\Odbc',
    'QueriesSgbdPgsql'    => '\BfwSql\Queries\SGBD\Pgsql',
    'QueriesSgbdSqlSrv'   => '\BfwSql\Queries\SGBD\SqlSrv',
    'QueriesSgbdSqlite'   => '\BfwSql\Queries\SGBD\Sqlite',
    
    'RunnerMonolog'       => '\BfwSql\Runners\Monolog',
    'RunnerObservers'     => '\BfwSql\Runners\Observers',
    'RunnerConnectDB'     => '\BfwSql\Runners\ConnectDB',
];
