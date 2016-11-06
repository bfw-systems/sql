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
abstract class Modeles extends \BFWSql\Sql
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
        $app       = \BFW\Application::getInstance();
        $listBases = $app->getModule('bfw-sql')->listBases;
        
        if (
            count($listBases) > 1
            && (
                empty($this->baseKeyName)
                || !isset($listBases[$this->baseKeyName])
            )
        ) {
            throw new Exception(
                'They are multiple connection, '
                .'so the property baseKeyName must be defined'
            );
        }
        
        if (count($listBases) === 1) {
            $sqlConnect = current($listBases);
        } else {
            $sqlConnect = $listBases[$this->baseKeyName];
        }
        
        parent::__construct($sqlConnect);
        $this->tableNameWithPrefix = $this->prefix.$this->tableName;
    }
}
