<?php

namespace BfwSql;

use \Exception;

/**
 * Abstract class for all Models class
 * 
 * @package bfw-sql
 * @author Vermeulen Maxime <bulton.fr@gmail.com>
 * @version 2.0
 */
abstract class Modeles extends \BfwSql\Sql
{
    /**
     * @var $tableName The table name
     */
    protected $tableName = '';
    
    /**
     * @var $tableNameWithPrefix the table name with prefix
     */
    protected $tableNameWithPrefix = '';
    
    /**
     * @var $baseKeyName The baseKeyName to use to connection.
     *  Use it if they are multiple database to connect in the application.
     */
    protected $baseKeyName = '';
    
    /**
     * Constructor
     */
    public function __construct()
    {
        $sqlConnect = $this->obtainSqlConnect();
        
        parent::__construct($sqlConnect);
        $this->tableNameWithPrefix = $this->prefix.$this->tableName;
    }
    
    /**
     * Get the BFW Application
     * It's a dedicated method for unit test or case where App is override
     * 
     * @return \BFW\Application
     */
    protected function getApp()
    {
        return \BFW\Application::getInstance();
    }
    
    /**
     * Obtain SqlConnect instance for the baseKeyName
     * 
     * @return \BfwSql\SqlConnect
     * 
     * @throws \Exception If there are many connection declared and if the
     *  property baseKeyName is empty
     */
    protected function obtainSqlConnect()
    {
        $listBases = $this->getApp()->getModule('bfw-sql')->listBases;
        
        if (count($listBases) === 0) {
            throw new Exception('There is no connection configured.');
        }
        
        if (count($listBases) > 1 && empty($this->baseKeyName)) {
            throw new Exception(
                'There are multiple connection, '
                .'so the property baseKeyName must be defined'
            );
        }
        
        if (count($listBases) > 1 && !isset($listBases[$this->baseKeyName])) {
            throw new Exception(
                'There are multiple connection, '
                .'but the connection '.$this->baseKeyName.' is not defined.'
            );
        }
        
        if (count($listBases) === 1) {
            $sqlConnect = current($listBases);
        } else {
            $sqlConnect = $listBases[$this->baseKeyName];
        }
        
        return $sqlConnect;
    }
}
