<?php

namespace BfwSql;

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
     */
    public function __construct(
        SqlConnect $sqlConnect,
        $tableName,
        $columns = null
    )
    {
        parent::__construct($sqlConnect);
        
        $prefix      = $sqlConnect->getConnectionInfos()->tablePrefix;
        $this->table = $prefix.$tableName;
        
        if (is_array($columns)) {
            $this->columns = $columns;
        }
    }
    
    /**
     * {@inheritdoc}
     */
    public function assembleRequest()
    {
        if (count($this->champs) === 0) {
            return;
        }
        
        $lstColumns = '';

        foreach ($this->columns as $columnName => $columnValue) {
            if ($lstColumns !== '') {
                $lstColumns .= ', ';
            }

            $lstColumns .= $columnName.'='.$columnValue;
        }

        
        $this->assembledRequest = 'UPDATE '.$this->table
            .' SET '.$lstColumns
            .$this->generateWhere();

        $this->callObserver();
    }
}
