<?php

namespace BfwSql\Queries\SGBD;

use \BfwSql\Queries\AbstractQuery;

abstract class AbstractSGBD
{
    /**
     * @var \BfwSql\Queries\AbstractQuery $querySystem The object who generate
     * the full query
     */
    protected $querySystem;
    
    /**
     * Define querySystem property
     * 
     * @param \BfwSql\Queries\AbstractQuery $querySystem
     */
    public function __construct(AbstractQuery $querySystem)
    {
        $this->querySystem = $querySystem;
    }
    
    /**
     * Getter accessor to property querySystem
     * 
     * @return \BfwSql\Queries\AbstractQuery
     */
    public function getQuerySystem(): AbstractQuery
    {
        return $this->querySystem;
    }
    
    /**
     * Return the current request type
     * 
     * @return string
     */
    protected function obtainRequestType(): string
    {
        return $this->querySystem->getRequestType();
    }
    
    /**
     * Define queries part to disable
     * 
     * @return array
     */
    protected function obtainPartsToDisable(): array
    {
        return [
            'delete' => [],
            'insert' => [],
            'select' => [],
            'update' => []
        ];
    }
    
    /**
     * Disable queries part define by method obtainPartsToDisable()
     * 
     * @param array &$queriesParts List of queries part
     * 
     * @return void
     */
    public function disableQueriesParts(array &$queriesParts)
    {
        $requestType = $this->obtainRequestType();
        $toDisable   = $this->obtainPartsToDisable();
        
        if (!isset($toDisable[$requestType])) {
            $toDisable[$requestType] = [];
        }
        
        if (empty($toDisable[$requestType])) {
            return;
        }
        
        foreach ($toDisable[$requestType] as $partName) {
            //Maybe called from AbstractQuery at this time
            if (!isset($queriesParts[$partName])) {
                continue;
            }
            
            $queriesParts[$partName]->setIsDisabled(true);
        }
    }
    
    /**
     * Generate an item in a list
     * 
     * @param string $expr The item expression
     * @param int $index The index of the item in the list
     * @param string $separator The separator to use between items
     * 
     * @return string
     */
    public function listItem(
        string $expr,
        int $index,
        string $separator
    ): string {
        $partSeparator = '';
        if ($index > 0) {
            $partSeparator .= $separator;
        }
        
        return $partSeparator.$expr;
    }
    
    /**
     * Generate the column name
     * 
     * @param string $colName The column name
     * @param string $tableName The table name of the column
     * @param bool $isFunction If the expr in colName is a function
     * @param bool $isJoker If the colName is the joker
     * 
     * @return string
     */
    public function columnName(
        string $colName,
        string $tableName,
        bool $isFunction,
        bool $isJoker
    ): string {
        if ($isFunction === true) {
            return $colName;
        } elseif ($isJoker) {
            return '`'.$tableName.'`.'.$colName;
        }
        
        return '`'.$tableName.'`.`'.$colName.'`';
    }
    
    /**
     * Generate the JOIN (left|right) part
     * 
     * @param string $tableName The table name
     * @param string|null $shortcut The shortcut for the table
     * @param string $on The on condition
     * 
     * @return string
     */
    public function join(string $tableName, $shortcut, string $on): string
    {
        $partQuery = '`'.$tableName.'`';
        if ($shortcut !== null) {
            $partQuery .= ' AS `'.$shortcut.'`';
        }
        
        $partQuery .= ' ON '.$on;
        
        return $partQuery;
    }
    
    /**
     * Generate the LIMIT part
     * 
     * @param int|null $rowCount The maximum number of rows to return
     * @param int|null $offset The offset of the first row to return
     * 
     * @return string
     */
    public function limit($rowCount, $offset): string
    {
        if ($rowCount === null) {
            return '';
        } else if ($offset === null) {
            return (string) $rowCount;
        } else {
            return $offset.', '.$rowCount;
        }
    }
    
    /**
     * Generate the ORDER part
     * 
     * @param string $expr The expression to use into the order
     * @param string|null $sort The sort order : ASC or DESC
     * @param bool $isFunction If the expression contain a function
     * 
     * @return string
     */
    public function order(string $expr, $sort, bool $isFunction): string
    {
        if ($isFunction === false) {
            $expr = '`'.$expr.'`';
        }
        
        if ($sort === null) {
            return $expr;
        }
        
        return $expr.' '.$sort;
    }
    
    /**
     * Generate the part for a sub-query
     * 
     * @param string $subQuery The request for the sub-query
     * @param string $shortcut The sub-query return shortcut
     * 
     * @return string
     */
    public function subQuery(string $subQuery, string $shortcut): string
    {
        return '('.$subQuery.') AS `'.$shortcut.'`';
    }
    
    /**
     * Generate the part for a table
     * 
     * @param string $name The table name
     * @param string|null $shortcut The table shortcut
     * 
     * @return string
     */
    public function table(string $name, $shortcut): string
    {
        $partQuery = '`'.$name.'`';
        
        if ($shortcut !== null) {
            $partQuery .= ' AS `'.$shortcut.'`';
        }
        
        return $partQuery;
    }
}
