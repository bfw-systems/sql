<?php

namespace BfwSql;

/**
 * Class to write DELETE queries
 * 
 * @package bfw-sql
 * @author Vermeulen Maxime <bulton.fr@gmail.com>
 * @version 2.0
 */
class SqlDelete extends SqlActions
{
    /**
     * Constructor
     * 
     * @param \BfwSql\SqlConnect $sqlConnect Instance of SGBD connexion
     * @param string             $tableName  The table name used for query
     */
    public function __construct(SqlConnect $sqlConnect, $tableName)
    {
        parent::__construct($sqlConnect);
        
        $prefix          = $sqlConnect->getConnectionInfos()->tablePrefix;
        $this->tableName = $prefix.$tableName;
    }
    
    /**
     * {@inheritdoc}
     */
    protected function assembleRequest()
    {
        $where = $this->generateWhere();
        
        $this->assembledRequest = 'DELETE FROM '.$this->tableName.$where;
        
        $this->callObserver();
    }
}
