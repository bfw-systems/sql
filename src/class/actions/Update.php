<?php

namespace BfwSql\Actions;

use \Exception;

/**
 * Class to write UPDATE queries
 * 
 * @package bfw-sql
 * @author Vermeulen Maxime <bulton.fr@gmail.com>
 * @version 2.0
 */
class Update extends AbstractActions
{
    /**
     * @const ERR_ASSEMBLE_NO_DATAS Exception code if no data to update.
     */
    const ERR_ASSEMBLE_NO_DATAS = 2305001;
    
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
        if (count($this->columns) === 0) {
            throw new Exception(
                'SqlUpdate : no datas to update.',
                self::ERR_ASSEMBLE_NO_DATAS
            );
        }
        
        $lstColumns = '';

        foreach ($this->columns as $columnName => $columnValue) {
            if ($lstColumns !== '') {
                $lstColumns .= ',';
            }
            
            if ($columnValue === null) {
                $fieldValue = 'null';
            } else {
                $fieldValue = $this->quoteValue($columnName, $columnValue);
            }

            $lstColumns .= '`'.$columnName.'`='.$fieldValue;
        }
        
        $this->assembledRequest = 'UPDATE '.$this->tableName
            .' SET '.$lstColumns
            .$this->generateWhere();
    }
}
