<?php

namespace BfwSql\Actions;

/**
 * Class to write INSERT INTO queries
 * 
 * @package bfw-sql
 * @author Vermeulen Maxime <bulton.fr@gmail.com>
 * @version 2.0
 */
class Insert extends AbstractActions
{
    /**
     * Constructor
     * 
     * @param \BfwSql\SqlConnect $sqlConnect Instance of SGBD connexion
     * @param string             $tableName  The table name used for query
     * @param array              $columns    (default: null) Datas to add
     *  Format is array('columnName' => 'value', ...);
     * @param string $quoteStatus (default: QUOTE_ALL) Status to automatic
     *  quoted string value system.
     */
    public function __construct(
        \BfwSql\SqlConnect $sqlConnect,
        $tableName,
        $columns = null,
        $quoteStatus = \BfwSql\Actions\AbstractActions::QUOTE_ALL
    ) {
        parent::__construct($sqlConnect);
        
        $this->quoteStatus = $quoteStatus;
        
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
            
            if ($columnValue === null) {
                $lstValues .= 'null';
                continue;
            }
            
            $lstValues .= $this->quoteValue($columnName, $columnValue);
        }
        
        $this->assembledRequest = 'INSERT INTO '.$this->tableName;
        
        if ($this->columns !== []) {
            $this->assembledRequest .= ' ('.$lstColumns.')'
                .' VALUES ('.$lstValues.')';
        }
    }
} 
