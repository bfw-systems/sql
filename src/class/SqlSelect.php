<?php

namespace BfwSql;

use \Exception;
use \PDO;

/**
 * Class to write SELECT queries
 * 
 * @package bfw-sql
 * @author Vermeulen Maxime <bulton.fr@gmail.com>
 * @version 2.0
 */
class SqlSelect extends SqlActions
{
    /**
     * @var string $returnType PHP Type used for return result
     */
    protected $returnType = '';
    
    /**
     * @var object $mainTable Informations about main table. Used for FROM part
     */
    protected $mainTable;
    
    /**
     * @var array $subQueries All sub-queries
     */
    protected $subQueries = [];
    
    /**
     * @var array $join List of all INNER JOIN
     */
    protected $join = [];
    
    /**
     * @var array $joinLeft List of all LEFT JOIN
     */
    protected $joinLeft = [];
    
    /**
     * @var array $joinRight List of all RIGHT JOIN
     */
    protected $joinRight = [];
    
    /**
     * @var string[] $order All columns used for ORDER BY part
     */
    protected $order = [];
    
    /**
     * @var string $limit The LIMIT part
     */
    protected $limit = '';
    
    /**
     * @var string[] $group The GROUP BY part
     */
    protected $group = [];
    
    /**
     * Constructor
     * 
     * @param \BfwSql\SqlConnect $sqlConnect Instance of SGBD connexion
     * @param string             $returnType PHP type used for return result
     */
    public function __construct(SqlConnect $sqlConnect, $returnType)
    {
        parent::__construct($sqlConnect);
        $this->returnType = $returnType;
    }
    
    /**
     * Define object used to save informations about a table
     * This object will be used to write query
     * 
     * @param string|array $table Tables informations
     * 
     * @return \stdClass
     */
    protected function obtainTableInfos($table)
    {
        if (!is_array($table) && !is_string($table)) {
            throw new Exception('Table information is not in the right format.');
        }
        
        if (is_array($table)) {
            $tableName = reset($table);
            $shortcut  = key($table);
        } else {
            $tableName = $table;
            $shortcut  = null;
        }
        
        $prefix = $this->sqlConnect->getConnectionInfos()->tablePrefix;
        
        return (object) [
            'tableName' => $prefix.$tableName,
            'shortcut'  => $shortcut
        ];
    }
    
    /**
     * Add columns for select
     * 
     * @param array|string $columns   Columns to add
     * @param string       $tableName Table name for will be columns added
     * 
     * @return void
     */
    protected function addColumnsForSelect($columns, $tableName)
    {
        if (!is_array($columns)) {
            $columns = (array) $columns;
        }
        
        foreach ($columns as $columnShortcut => $columnName) {
            //If value is a sql function or keyword, not add quote
            if (
                strpos($columnName, ' ') === false
                && strpos($columnName, '(') === false
            ) {
                //Add quote only if a column has been declared
                if ($columnName !== '*') {
                    $columnName = '`'.$columnName.'`';
                }
                
                $columnName = '`'.$tableName.'`.'.$columnName;
            }
            
            //If a column shortcut is declared
            if (is_string($columnShortcut)) {
                $this->columns[] = (object) [
                    'column'   => $columnName,
                    'shortcut' => $columnShortcut
                ];
            } else {
                $this->columns[] = (object) [
                    'column'   => $columnName,
                    'shortcut' => null
                ];
            }
        }
    }
    
    /**
     * Declare information for FROM part and column will be get for main table
     * 
     * @param string|array $table   Table name.
     *  It can be an array if a table shortcut is declared.
     *  In array mode, the format is ['asValue' => 'tableName']
     * @param string|array $columns (default: "*") Columns will be get for
     *  the table declared in first argument
     * 
     * @return \BfwSql\SqlSelect
     */
    public function from($table, $columns = '*')
    {
        $this->mainTable = $this->obtainTableInfos($table);
        
        $tableName = $this->mainTable->tableName;
        if ($this->mainTable->shortcut !== null) {
            $tableName = $this->mainTable->shortcut;
        }
        
        $this->addColumnsForSelect($columns, $tableName);
        
        return $this;
    }
    
    /**
     * Add a sub-query in the SELECT part on the request
     * 
     * @param \BfwSql\SqlActions|string $subRequest The sub-request
     * @param string                    $shortcut   The shortcut to use for
     *  this query in SELECT part
     * 
     * @return \BfwSql\SqlSelect
     */
    public function subQuery($subRequest, $shortcut)
    {
        if (
            is_object($subRequest)
            && $subRequest instanceof \BfwSql\SqlActions
        ) {
            $subQuery = $subRequest->assemble();
        } elseif (is_string($subRequest)) {
            $subQuery = $subRequest;
        } else {
            throw new Exception(
                'subRequest passed in parameters must be an instance of '
                .'BfwSql System or a string.'
            );
        }
        
        $this->subQueries[] = (object) [
            'query'    => $subQuery,
            'shortcut' => $shortcut
        ];
        
        return $this;
    }
    
    /**
     * Add a (inner|left|right) join to the request
     * 
     * @param string       $joinPropertyName The name of the property in this
     *  class where the join is add
     * @param string|array $table            Name of the table concerned by
     * the join. Or an array with the table shortcut in key.
     * @param string       $joinOn           SQL part "ON" for this join
     * @param string|array $joinColumns      Columns from the table joined to
     *  add in the SELECT part of the request
     * 
     * @return \BfwSql\SqlSelect
     */
    protected function createJoin(
        $joinPropertyName,
        $table,
        $joinOn,
        $joinColumns
    ) {
        $tableInfos     = $this->obtainTableInfos($table);
        $tableInfos->on = $joinOn;
        
        $tableName = $tableInfos->tableName;
        if ($tableInfos->shortcut !== null) {
            $tableName = $tableInfos->shortcut;
        }
        
        $this->{$joinPropertyName}[] = $tableInfos;
        $this->addColumnsForSelect($joinColumns, $tableName);
        
        return $this;
    }
    
    /**
     * Add a INNER JOIN to the request
     * 
     * @param string|array $table       Name of the table concerned by the
     *  join. Or an array with the table shortcut in key.
     * @param string       $joinOn      SQL part "ON" for this join
     * @param string|array $joinColumns Columns from the table joined to add
     *  in the SELECT part of the request
     * 
     * @return \BfwSql\SqlSelect
     */
    public function join($table, $joinOn, $joinColumns = '*')
    {
        return $this->createJoin('join', $table, $joinOn, $joinColumns);
    }
    
    /**
     * Add a LEFT JOIN to the request
     * 
     * @param string|array $table       Name of the table concerned by the
     *  join. Or an array with the table shortcut in key.
     * @param string       $joinOn      SQL part "ON" for this join
     * @param string|array $joinColumns Columns from the table joined to add
     *  in the SELECT part of the request
     * 
     * @return \BfwSql\SqlSelect
     */
    public function joinLeft($table, $joinOn, $joinColumns = '*')
    {
        return $this->createJoin('joinLeft', $table, $joinOn, $joinColumns);
    }
    
    /**
     * Add a RIGHT JOIN to the request
     * 
     * @param string|array $table       Name of the table concerned by the
     *  join. Or an array with the table shortcut in key.
     * @param string       $joinOn      SQL part "ON" for this join
     * @param string|array $joinColumns Columns from the table joined to add
     *  in the SELECT part of the request
     * 
     * @return \BfwSql\SqlSelect
     */
    public function joinRight($table, $joinOn, $joinColumns = '*')
    {
        return $this->createJoin('joinRight', $table, $joinOn, $joinColumns);
    }
    
    /**
     * Add a order condition to the request for the ORDER BY part
     * 
     * @param string $condition The new condition
     * 
     * @return \BfwSql\SqlSelect
     */
    public function order($condition)
    {
        $this->order[] = (string) $condition;
        return $this;
    }
    
    /**
     * Add information about the LIMIT part in request
     * 
     * @param array|integer $limit If it's a integer, the number of row to
     *  return. If an array, the format is [numberToStart, numberOfRowToReturn]
     * 
     * @return \BfwSql\SqlSelect
     */
    public function limit($limit)
    {
        if (!is_array($limit)) {
            $limit = (array) $limit;
        }
        
        if (isset($limit[1])) {
            $this->limit = $limit[0].', '.$limit[1];
        } else {
            $this->limit = (string) $limit[0];
        }
        
        return $this;
    }
    
    /**
     * Add a GROUP BY part to the request
     * 
     * @param string $condition The condition to use in GROUP BY
     * 
     * @return \BfwSql\SqlSelect
     */
    public function group($condition)
    {
        $this->group[] = $condition;
        return $this;
    }
    
    /**
     * Return the PDO constant for the returnType declared
     * 
     * @return integer
     */
    protected function obtainPdoFetchType()
    {
        if ($this->returnType === 'object') {
            return PDO::FETCH_OBJ;
        }
        
        return PDO::FETCH_ASSOC;
    }
    
    /**
     * Fetch one row of the result
     * 
     * @return mixed
     */
    public function fetchRow()
    {
        $req = $this->execute();
        return $req->fetch($this->obtainPdoFetchType());
    }
    
    /**
     * Fetch all rows returned by the request
     * 
     * @return generator
     */
    public function fetchAll()
    {
        $request = $this->execute(); //throw an Exception if error
        
        while ($row = $request->fetch($this->obtainPdoFetchType())) {
            yield $row;
        }
    }
    
    /**
     * {@inheritdoc}
     */
    protected function assembleRequest()
    {
        $this->assembledRequest = 'SELECT '.$this->generateSelect()
            .' FROM '.$this->generateFrom()
            .$this->generateJoin('join')
            .$this->generateJoin('joinLeft')
            .$this->generateJoin('joinRight')
            .$this->generateWhere()
            .$this->generateGroupBy()
            .$this->generateOrderBy()
            .$this->generateLimit();
        
        $this->callObserver();
    }
    
    /**
     * Write the SELECT part of the request
     * 
     * @return string
     */
    protected function generateSelect()
    {
        $select = '';
        foreach ($this->columns as $columnInfos) {
            if ($select != '') {
                $select .= ', ';
            }
            
            $select .= $columnInfos->column;
            if ($columnInfos->shortcut !== null) {
                $select .= ' AS `'.$columnInfos->shortcut.'`';
            }
        }
        
        foreach ($this->subQueries as $subQueryInfos) {
            if ($select != '') {
                $select .= ', ';
            }

            $select .= '('.$subQueryInfos->query.')'
                .' AS `'.$subQueryInfos->shortcut.'`';
        }
        
        return $select;
    }
    
    /**
     * Write the FROM part of the request
     * 
     * @return string
     */
    protected function generateFrom()
    {
        $from = '`'.$this->mainTable->tableName.'`';
        
        if ($this->mainTable->shortcut !== null) {
            $from .= ' AS `'.$this->mainTable->shortcut.'`';
        }
        
        return $from;
    }
    
    /**
     * Write a (inner|left|right) join in the request
     * 
     * @param string $joinProperty The join property name
     * 
     * @return string
     */
    protected function generateJoin($joinProperty)
    {
        $join = '';
        if (count($this->{$joinProperty}) === 0) {
            return $join;
        }
        
        if ($joinProperty == 'join') {
            $joinSqlName = ' INNER JOIN ';
        } elseif ($joinProperty == 'joinLeft') {
            $joinSqlName = ' LEFT JOIN ';
        } elseif ($joinProperty == 'joinRight') {
            $joinSqlName = ' RIGHT JOIN ';
        }

        foreach ($this->{$joinProperty} as $joinInfos) {
            $join .= $joinSqlName.'`'.$joinInfos->tableName.'`';
            if ($joinInfos->shortcut !== null) {
                $join .= ' AS `'.$joinInfos->shortcut.'`';
            }

            $join .= ' ON '.$joinInfos->on;
        }
        
        return $join;
    }
    
    /**
     * Write the ORDER BY part for the request
     * 
     * @return string
     */
    protected function generateOrderBy()
    {
        if (count($this->order) === 0) {
            return '';
        }
        
        $order = ' ORDER BY ';
        foreach ($this->order as $orderCondition) {
            if ($order != ' ORDER BY ') {
                $order .= ', ';
            }
            
            $order .= $orderCondition;
        }
        
        return $order;
    }
    
    /**
     * Write the GRUOP BY part for the request
     * 
     * @return string
     */
    protected function generateGroupBy()
    {
        if (count($this->group) === 0) {
            return '';
        }
        
        $group = ' GROUP BY ';
        foreach ($this->group as $groupCondition) {
            if ($group != ' GROUP BY ') {
                $group .= ', ';
            }
            
            $group .= $groupCondition;
        }
        
        return $group;
    }
    
    /**
     * Write the LIMIT part for the request
     * 
     * @return string
     */
    protected function generateLimit()
    {
        $limit = '';
        if ($this->limit !== '') {
            $limit = ' LIMIT '.$this->limit;
        }
        
        return $limit;
    }
} 
