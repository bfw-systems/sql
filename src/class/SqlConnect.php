<?php

namespace BfwSql;

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
     * @param \stdClass $connectionInfos All informations about the connection
     * 
     * @throw \PDOException If Connexion fail
     */
    public function __construct($connectionInfos)
    {
        $this->connectionInfos = $connectionInfos;
        
        $host     = $connectionInfos->host;
        $baseName = $connectionInfos->baseName;
        
        $this->type = $connectionInfos->baseType;
        $this->PDO  = new \PDO(
            $this->type.':host='.$host.';dbname='.$baseName,
            $connectionInfos->user,
            $connectionInfos->password
        );
        
        if ($connectionInfos->useUTF8 === true) {
            $this->setUtf8($connectionInfos->useUTF8);
        }
    }
    
    /**
     * Define charset to UTF-8 with mysql
     * 
     * @return void
     */
    protected function setUtf8()
    {
        $this->PDO->exec('SET NAMES utf8');
    }
    
    /**
     * Protect a data with the system implemented by the pdo drivers used.
     * 
     * @param string $string Data to protect
     * 
     * @return string
     */
    public function protect($string)
    {
        $protectedString = $this->PDO->quote($string);
        
        /**
         * The quote method add the caracter ' on the start and the end of the
         * protected string.
         * So we remove this quote at the start and the end of the string.
         */
        return substr($protectedString, 1, strlen($protectedString)-2);
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
     * @return \stdClass
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
    public function getType() 
    {
        return $this->type;
    }
    
    /**
     * Getter to access at the property nbQuery
     * 
     * @return integer
     */
    public function getNbQuery()
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
