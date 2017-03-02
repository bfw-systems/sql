<?php

namespace BfwSql;

use \Exception;

/**
 * Class to write UPDATE queries
 * 
 * @package bfw-sql
 * @author Vermeulen Maxime <bulton.fr@gmail.com>
 * @version 2.0
 */
class SqlUpdate extends SqlActions
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
        SqlConnect $sqlConnect,
        $tableName,
        $columns = null,
        $quoteStatus = \BfwSql\SqlActions::QUOTE_ALL
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
        if (count($this->columns) === 0) {
            throw new Exception('SqlUpdate : no datas to update.');
        }
        
        $lstColumns = '';

        foreach ($this->columns as $columnName => $columnValue) {
            if ($lstColumns !== '') {
                $lstColumns .= ',';
            }

            $lstColumns .= '`'.$columnName.'`='
                .$this->quoteValue($columnName, $columnValue);
        }
        
        $this->assembledRequest = 'UPDATE '.$this->tableName
            .' SET '.$lstColumns
            .$this->generateWhere();

        $this->callObserver();
    }
}
