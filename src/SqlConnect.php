<?php

namespace BfwSql;

use \Exception;

/**
 * Class to initialize a connection to a SQL server with PDO
 * 
 * @package bfw-sql
 * @author Vermeulen Maxime <bulton.fr@gmail.com>
 * @version 2.0
 */
class SqlConnect
{
    /**
     * @const ERR_DSN_METHOD_NOT_FOUND Exception code if there is no method to
     * generate the dsn used by PDO.
     */
    const ERR_DSN_METHOD_NOT_FOUND = 2104001;
    
    /**
     * @var \PDO $PDO PDO Connexion object
     */
    protected $PDO;
    
    /**
     * @var \stdClass $connectionInfos All informations about the connection
     */
    protected $connectionInfos;
    
    /**
     * @var string $type Connexion type (mysql/pgsql/etc)
     */
    protected $type;
    
    /**
     * @var integer $nbQuery (default 0) Number of request has been done
     */
    protected $nbQuery = 0;
    
    /**
     * Constructor
     * Initialize the connection
     * 
     * @param object $connectionInfos All informations about the connection
     * 
     * @throw \PDOException If Connexion fail
     */
    public function __construct($connectionInfos)
    {
        $this->connectionInfos = $connectionInfos;
        $this->type            = &$this->connectionInfos->baseType;
    }
    
    /**
     * Initialize the connection
     * 
     * @throw \PDOException If Connexion fail
     * 
     * @return void
     */
    public function createConnection()
    {
        $usedClass          = \BfwSql\UsedClass::getInstance();
        $createDsnClassName = $usedClass->obtainClassNameToUse('CreatePdoDsn');
        $pdoClassName       = $usedClass->obtainClassNameToUse('PDO');
        
        if (!method_exists($createDsnClassName, $this->type)) {
            throw new Exception(
                'No method to generate DSN find on \BfwSql\CreatePdoDsn class.',
                self::ERR_DSN_METHOD_NOT_FOUND
            );
        }
        
        $this->PDO = new $pdoClassName(
            $createDsnClassName::{$this->type}($this->connectionInfos),
            $this->connectionInfos->user,
            $this->connectionInfos->password,
            $this->connectionInfos->pdoOptions
        );
            
        if ($this->connectionInfos->mysqlUtf8 === true) {
            $this->mysqlUtf8();
        }
    }
    
    /**
     * Define charset to UTF-8 with mysql
     * 
     * @return void
     */
    protected function mysqlUtf8()
    {
        $this->PDO->exec('SET NAMES utf8');
    }
    
    /**
     * Protect a data with the system implemented by the pdo drivers used.
     * 
     * @param string $datas Data to protect
     * 
     * @return string
     */
    public function protect(string $datas): string
    {
        $protectedString = $this->PDO->quote($datas);
        
        /**
         * The quote method add the caracter ' on the start and the end of the
         * protected string.
         * So we remove this quote at the start and the end of the string.
         */
        return substr($protectedString, 1, strlen($protectedString) - 2);
    }
    
    /**
     * Getter to access at the property PDO
     * 
     * @return \PDO
     */
    public function getPDO()
    {
        return $this->PDO;
    }
    
    /**
     * Getter to access at the property connectionInfos
     * 
     * @return object
     */
    public function getConnectionInfos()
    {
        return $this->connectionInfos;
    }
    
    /**
     * Getter to access at the property type
     * 
     * @return string
     */
    public function getType() : string
    {
        return $this->type;
    }
    
    /**
     * Getter to access at the property nbQuery
     * 
     * @return integer
     */
    public function getNbQuery(): int
    {
        return $this->nbQuery;
    }
    
    /**
     * Increment the number of query has been done
     * 
     * @return void
     */
    public function upNbQuery()
    {
        $this->nbQuery++;
    }
}
