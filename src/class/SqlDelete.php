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
        
        $prefix      = $sqlConnect->getConnectionInfos()->tablePrefix;
        $this->table = $prefix.$tableName;
    }
    
    /**
     * {@inheritdoc}
     */
    public function assembleRequest()
    {
        $where = $this->generateWhere();
        
        $this->RequeteAssembler = 'DELETE FROM '.$this->table.$where;
        
        $this->callObserver();
    }
}
