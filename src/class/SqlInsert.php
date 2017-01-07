<?php

namespace BfwSql;

/**
 * Class to write INSERT INTO queries
 * 
 * @package bfw-sql
 * @author Vermeulen Maxime <bulton.fr@gmail.com>
 * @version 2.0
 */
class SqlInsert extends SqlActions
{
    /**
     * Constructor
     * 
     * @param \BfwSql\SqlConnect $sqlConnect Instance of SGBD connexion
     * @param string             $tableName  The table name used for query
     * @param array              $columns    (default: null) Datas to add
     *  Format is array('columnName' => 'value', ...);
     */
    public function __construct(
        SqlConnect $sqlConnect,
        $tableName,
        $columns = null
    ) {
        parent::__construct($sqlConnect);
        
        $prefix          = $sqlConnect->getConnectionInfos()->tablePrefix;
        $this->tableName = $prefix.$tableName;
        
        if (is_array($columns)) {
            $this->columns = $columns;
        }
    }
    
    /**
     * {@inheritdoc}
     */
    protected function assembleRequest()
    {
        $lstColumns    = '';
        $lstValues     = '';
        $indexReadList = -1;
        
        foreach ($this->columns as $columnName => $columnValue) {
            $indexReadList++;
            if ($indexReadList > 0) {
                $lstColumns .= ',';
                $lstValues  .= ',';
            }
            
            $lstColumns .= '`'.$columnName.'`';
            $lstValues  .= $columnValue;
        }
        
        $this->assembledRequest = 'INSERT INTO '.$this->tableName;
        
        if ($this->columns !== []) {
            $this->assembledRequest .= ' ('.$lstColumns.')'
                .' VALUES ('.$lstValues.')';
        }
        
        $this->callObserver();
    }
} 
