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
 * Model is extended by user.
 */
return [
    'PDO'                         => '\PDO',
    'CreatePdoDsn'                => '\BfwSql\CreatePdoDsn',
    'SqlConnect'                  => '\BfwSql\SqlConnect',
            
    'ExecutersCommon'             => '\BfwSql\Executers\Common',
    'ExecutersSelect'             => '\BfwSql\Executers\Select',
            
    'QueriesDelete'               => '\BfwSql\Queries\Delete',
    'QueriesInsert'               => '\BfwSql\Queries\Insert',
    'QueriesSelect'               => '\BfwSql\Queries\Select',
    'QueriesUpdate'               => '\BfwSql\Queries\Update',
    
    'QueriesPartsColumn'          => '\BfwSql\Queries\Parts\Column',
    'QueriesPartsColumnList'      => '\BfwSql\Queries\Parts\ColumnList',
    'QueriesPartsColumnValueList' => '\BfwSql\Queries\Parts\ColumnValueList',
    'QueriesPartsCommonList'      => '\BfwSql\Queries\Parts\CommonList',
    'QueriesPartsJoin'            => '\BfwSql\Queries\Parts\Join',
    'QueriesPartsJoinList'        => '\BfwSql\Queries\Parts\JoinList',
    'QueriesPartsLimit'           => '\BfwSql\Queries\Parts\Limit',
    'QueriesPartsOrder'           => '\BfwSql\Queries\Parts\Order',
    'QueriesPartsOrderList'       => '\BfwSql\Queries\Parts\OrderList',
    'QueriesPartsSubQuery'        => '\BfwSql\Queries\Parts\SubQuery',
    'QueriesPartsSubQueryList'    => '\BfwSql\Queries\Parts\SubQueryList',
    'QueriesPartsTable'           => '\BfwSql\Queries\Parts\Table',
    'QueriesPartsWhereList'       => '\BfwSql\Queries\Parts\WhereList',
    
    'QueriesSgbdCubrid'           => '\BfwSql\Queries\SGBD\Cubrid',
    'QueriesSgbdDblib'            => '\BfwSql\Queries\SGBD\Dblib',
    'QueriesSgbdFirebird'         => '\BfwSql\Queries\SGBD\Firebird',
    'QueriesSgbdIbe'              => '\BfwSql\Queries\SGBD\Ibe',
    'QueriesSgbdInformix'         => '\BfwSql\Queries\SGBD\Informix',
    'QueriesSgbdMysql'            => '\BfwSql\Queries\SGBD\Mysql',
    'QueriesSgbdOci'              => '\BfwSql\Queries\SGBD\Oci',
    'QueriesSgbdOdbc'             => '\BfwSql\Queries\SGBD\Odbc',
    'QueriesSgbdPgsql'            => '\BfwSql\Queries\SGBD\Pgsql',
    'QueriesSgbdSqlSrv'           => '\BfwSql\Queries\SGBD\SqlSrv',
    'QueriesSgbdSqlite'           => '\BfwSql\Queries\SGBD\Sqlite',
            
    'RunnerMonolog'               => '\BfwSql\Runners\Monolog',
    'RunnerObservers'             => '\BfwSql\Runners\Observers',
    'RunnerConnectDB'             => '\BfwSql\Runners\ConnectDB',
];
