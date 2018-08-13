<?php

namespace BfwSql;

use \Exception;

/**
 * All function to create pdo instance for all pdo drivers
 * @link http://php.net/manual/fr/pdo.drivers.php
 */
class CreatePdoDsn
{
    /**
     * @const ERR_UNKNOWN_FORMAT Exception code if the format is unknown.
     */
    const ERR_UNKNOWN_FORMAT = 2102001;
    
    /**
     * @throws \Exception Unknown DSN format
     */
    protected static function unknownDsn()
    {
        throw new Exception(
            'Sorry, the DSN drivers string is not declared in bfw-sql module.'
            .'The main raison is the author don\'t know dsn format.'
            .'You can create an issue on github and give the correct format or'
            .', better, create a pull-request.',
            self::ERR_UNKNOWN_FORMAT
        );
    }
    
    /**
     * Create the PDO instance for mysql driver
     * 
     * @param object $connectionInfos All informations about the connection
     * 
     * @return string
     */
    public static function mysql($connectionInfos): string
    {
        $host     = $connectionInfos->host;
        $port     = $connectionInfos->port;
        $baseName = $connectionInfos->baseName;
        
        return 'mysql:host='.$host.';port='.$port.';dbname='.$baseName;
    }
    
    /**
     * Create the PDO instance for sqlite driver
     * 
     * @param object $connectionInfos All informations about the connection
     * 
     * @return string
     */
    public static function sqlite($connectionInfos): string
    {
        return 'sqlite:'.$connectionInfos->filePath;
    }
    
    /**
     * Create the PDO instance for pgsql driver
     * 
     * @param object $connectionInfos All informations about the connection
     * 
     * @return string
     */
    public static function pgsql($connectionInfos): string
    {
        $host     = $connectionInfos->host;
        $port     = $connectionInfos->port;
        $baseName = $connectionInfos->baseName;
        
        return 'pgsql:host='.$host.';port='.$port.';dbname='.$baseName;
    }
    
    /**
     * Create the PDO instance for cubrid driver
     * 
     * DSN find on http://php.net/manual/en/ref.pdo-cubrid.php
     * If is not correct, please, create a github issue.
     * 
     * @param object $connectionInfos All informations about the connection
     * 
     * @return string
     */
    public static function cubrid($connectionInfos): string
    {
        $host     = $connectionInfos->host;
        $port     = $connectionInfos->port;
        $baseName = $connectionInfos->baseName;
        
        return 'cubrid:dbname='.$baseName.';host='.$host.';port='.$port;
    }
    
    /**
     * Create the PDO instance for dblib driver
     * 
     * DSN find on http://php.net/manual/fr/ref.pdo-dblib.php#118093
     * If is not correct, please, create a github issue.
     * 
     * @param object $connectionInfos All informations about the connection
     * 
     * @return string
     */
    public static function dblib($connectionInfos): string
    {
        $host     = $connectionInfos->host;
        $port     = $connectionInfos->port;
        $baseName = $connectionInfos->baseName;
        
        return 'dblib:host='.$host.':'.$port.';dbname='.$baseName;
    }
    
    /**
     * Create the PDO instance for firebird driver
     * 
     * @param object $connectionInfos All informations about the connection
     * 
     * @throws \Exception Unknown DSN format
     */
    public static function firebird($connectionInfos)
    {
        self::unknownDsn();
    }
    
    /**
     * Create the PDO instance for ibm driver
     * 
     * @param object $connectionInfos All informations about the connection
     * 
     * @throws \Exception Unknown DSN format
     */
    public static function ibm($connectionInfos)
    {
        self::unknownDsn();
    }
    
    /**
     * Create the PDO instance for informix driver
     * 
     * @param object $connectionInfos All informations about the connection
     * 
     * @throws \Exception Unknown DSN format
     */
    public static function informix($connectionInfos)
    {
        self::unknownDsn();
    }
    
    /**
     * Create the PDO instance for sqlsrv driver
     * 
     * @param object $connectionInfos All informations about the connection
     * 
     * @throws \Exception Unknown DSN format
     */
    public static function sqlsrv($connectionInfos)
    {
        self::unknownDsn();
    }
    
    /**
     * Create the PDO instance for oci driver
     * 
     * @param object $connectionInfos All informations about the connection
     * 
     * @throws \Exception Unknown DSN format
     */
    public static function oci($connectionInfos)
    {
        self::unknownDsn();
    }
    
    /**
     * Create the PDO instance for odbc driver
     * 
     * @param object $connectionInfos All informations about the connection
     * 
     * @throws \Exception Unknown DSN format
     */
    public static function odbc($connectionInfos)
    {
        self::unknownDsn();
    }
    
    /**
     * Create the PDO instance for 4d driver
     * 
     * @param object $connectionInfos All informations about the connection
     * 
     * @throws \Exception Unknown DSN format
     *
    //Error, function name couldn't start with a number
    public static function 4d($connectionInfos)
    {
        self::unknownDsn();
    }
    */
}
