<?php

namespace BfwSql\Actions;

use \Exception;

/**
 * Abstract class used for all query writer class.
 * 
 * @package bfw-sql
 * @author Vermeulen Maxime <bulton.fr@gmail.com>
 * @version 2.0
 */
abstract class AbstractActions
{
    /**
     * @const QUOTE_ALL To automatic quote all string values.
     * Used by SqlInsert and SqlUpdate.
     */
    const QUOTE_ALL = 'all';
    
    /**
     * @const QUOTE_ALL To not automatic quote string values.
     * Used by SqlInsert and SqlUpdate.
     */
    const QUOTE_NONE = 'none';
    
    /**
     * @const QUOTE_ALL To automatic quote string values only for somes columns
     * Used by SqlInsert and SqlUpdate.
     */
    const QUOTE_PARTIALLY = 'partially';
    
    /**
     * @const PARTIALLY_MODE_QUOTE Used by automatic quote system when is equal
     * to QUOTE_PARTIALLY. Define default quote mode to quote all columns which
     * is not define to not be quoted.
     */
    const PARTIALLY_MODE_QUOTE = 'quote';
    
    /**
     * @const PARTIALLY_MODE_NOTQUOTE Used by automatic quote system when is
     * equal to QUOTE_PARTIALLY. Define default quote mode to not quote all
     * columns which is not define to be quoted.
     */
    const PARTIALLY_MODE_NOTQUOTE = 'not quote';
    
    /**
     * @var \BfwSql\SqlConnect $sqlConnect SqlConnect object
     */
    protected $sqlConnect;
    
    /**
     * @var string $assembledRequest The request will be executed
     */
    protected $assembledRequest = '';
    
    /**
     * @var boolean $isPreparedRequest If is a prepared request
     */
    protected $isPreparedRequest = true;
    
    /**
     * @var string $tableName The main table name for request
     */
    protected $tableName = '';
    
    /**
     * @var array $columns List of impacted columns by the request
     */
    protected $columns = array();
    
    /**
     * @var string $quoteStatus The current automic quote status.
     */
    protected $quoteStatus = self::QUOTE_ALL;
    
    /**
     * @var string $partiallyPreferedMode The default mode to use on column
     * when quoteStatus is declared to be PARTIALLY.
     * Value is self::PARTIALLY_MODE_QUOTE or self::PARTIALLY_MODE_NOTQUOTE
     */
    protected $partiallyPreferedMode = self::PARTIALLY_MODE_QUOTE;
    
    /**
     * @var array $quotedColumns List of columns where value will be quoted if
     * is string.
     */
    protected $quotedColumns = [];
    
    /**
     * @var array $notQuotedColumns List of columns where value will not be
     * quoted if is string.
     */
    protected $notQuotedColumns = [];
    
    /**
     * @var string[] $where All filter use in where part of the request
     */
    protected $where = array();
    
    /**
     * @var string[] $preparedRequestArgs Arguments used by prepared request
     */
    protected $preparedRequestArgs = array();
    
    /**
     * @var array $prepareDriversOptions SGBD driver option used for
     *  prepared request
     * 
     * @link http://php.net/manual/en/pdo.prepare.php
     */
    protected $prepareDriversOptions = array();
    
    /**
     * @var boolean $noResult If request has sent no result.
     */
    protected $noResult = false;
    
    /**
     * @var \PDOStatement $lastRequestStatement The PDOStatement pour the
     *  last request executed.
     */
    protected $lastRequestStatement;
    
    /**
     * @var array $lastErrorInfos The PDO::errorInfos return for the last
     * query executed. Empty if no request has been executed.
     */
    protected $lastErrorInfos = [];
    
    /**
     * Constructor
     * 
     * @param \BfwSql\SqlConnect $sqlConnect Instance of SGBD connexion
     */
    public function __construct(\BfwSql\SqlConnect $sqlConnect)
    {
        $this->sqlConnect = $sqlConnect;
    }
    
    /**
     * Getter to access at sqlConnect property
     * 
     * @return \BfwSql\SqlConnect
     */
    public function getSqlConnect()
    {
        return $this->sqlConnect;
    }
    
    /**
     * Getter to access to assembledRequest property
     * 
     * @return string
     */
    public function getAssembledRequest()
    {
        return $this->assembledRequest;
    }

    /**
     * Getter to access to isPreparedRequest property
     * 
     * @return boolean
     */
    public function getIsPreparedRequest()
    {
        return $this->isPreparedRequest;
    }
    
    /**
     * Setter to enable or disable prepared request
     * 
     * @param boolean $preparedRequestStatus The new status for prepared request
     * 
     * @return \BfwSql\Actions\AbstractActions
     */
    public function setIsPreparedRequest($preparedRequestStatus)
    {
        $this->isPreparedRequest = (bool) $preparedRequestStatus;
        
        return $this;
    }

    /**
     * Getter to access to tableName property
     * 
     * @return string
     */
    public function getTableName()
    {
        return $this->tableName;
    }

    /**
     * Getter to access to columns property
     * 
     * @return array
     */
    public function getColumns()
    {
        return $this->columns;
    }

    /**
     * Getter to access to quoteStatus property
     * 
     * @return string
     */
    public function getQuoteStatus()
    {
        return $this->quoteStatus;
    }
    
    /**
     * Getter to access to partiallyPreferedMode property
     * 
     * @return string
     */
    public function getPartiallyPreferedMode()
    {
        return $this->partiallyPreferedMode;
    }

    /**
     * Getter to access to partiallyPreferedMode property
     * Value should be self::PARTIALLY_MODE_QUOTE or
     * self::PARTIALLY_MODE_NOTQUOTE
     * 
     * @param string $partiallyPreferedMode The new prefered mode
     * 
     * @return $this
     */
    public function setPartiallyPreferedMode($partiallyPreferedMode)
    {
        $this->partiallyPreferedMode = $partiallyPreferedMode;
        
        return $this;
    }
    
    /**
     * Getter to access to quotedColumns property
     * 
     * @return array
     */
    public function getQuotedColumns()
    {
        return $this->quotedColumns;
    }

    /**
     * Getter to access to notQuotedColumns property
     * 
     * @return array
     */
    public function getNotQuotedColumns()
    {
        return $this->notQuotedColumns;
    }

    /**
     * Getter to access to where property
     * 
     * @return string[]
     */
    public function getWhere()
    {
        return $this->where;
    }
    
    /**
     * Getter to access at preparedRequestArgs property
     * 
     * @return array
     */
    public function getPreparedRequestArgs()
    {
        return $this->preparedRequestArgs;
    }
    
    /**
     * Getter to access at prepareDriversOptions property
     * 
     * @return array
     */
    public function getPrepareDriversOptions()
    {
        return $this->prepareDriversOptions;
    }
    
    /**
     * Define driver options to prepared request
     * 
     * @link http://php.net/manual/fr/pdo.prepare.php
     * 
     * @param array $driverOptions Drivers options
     * 
     * @return \BfwSql\Actions\AbstractActions
     */
    public function setPrepareDriversOptions(array $driverOptions)
    {
        $this->prepareDriversOptions = $driverOptions;
        
        return $this;
    }

    /**
     * Getter to access to noResult property
     * 
     * @return boolean
     */
    public function getNoResult()
    {
        return $this->noResult;
    }

    /**
     * Getter to access to lastRequestStatement property
     * 
     * @return \PDOStatement|null
     */
    public function getLastRequestStatement()
    {
        return $this->lastRequestStatement;
    }
    
    /**
     * Getter to access to lastErrorInfos property
     * 
     * @return array
     */
    public function getLastErrorInfos()
    {
        return $this->lastErrorInfos;
    }
    
    /**
     * Check if a request is assemble or not.
     * If not, run the method assembleRequest.
     * 
     * @return boolean
     */
    public function isAssembled()
    {
        if ($this->assembledRequest === '') {
            return false;
        }
        
        return true;
    }
    
    /**
     * Write the query
     * 
     * @return void
     */
    protected abstract function assembleRequest();
    
    /**
     * Return the assembled request
     * 
     * @param boolean $force : Force to re-assemble request
     * 
     * @return string
     */
    public function assemble($force = false)
    {
        if ($this->isAssembled() === false || $force === true) {
            $this->assembleRequest();
        }
        
        return $this->assembledRequest;
    }
    
    /**
     * Execute the assembled request
     * 
     * @return array The pdo errorInfo array
     */
    protected function executeQuery()
    {
        $pdo = $this->sqlConnect->getPDO();
        $this->sqlConnect->upNbQuery();
        $this->assemble();
        
        if ($this->isPreparedRequest) {
            $req = $pdo->prepare(
                $this->assembledRequest,
                $this->prepareDriversOptions
            );
            
            $req->execute($this->preparedRequestArgs);
            $error = $pdo->errorInfo();
        } else {
            $pdoMethodToCall = 'exec';
            if ($this instanceof \BfwSql\Actions\Select) {
                $pdoMethodToCall = 'query';
            }
            
            $req   = $pdo->{$pdoMethodToCall}($this->assembledRequest);
            $error = $pdo->errorInfo();
        }
        
        $this->lastRequestStatement = $req;
        $this->lastErrorInfos       = $error;
        
        return $error;
    }
    
    /**
     * Execute the assembled request and check if there are errors
     * Update property noResult
     * 
     * @throws \Exception If the request fail
     * 
     * @return \PDOStatement|integer
     */
    public function execute()
    {
        $error = $this->executeQuery();
        
        //Throw an exception if they are an error with the request
        if ($error[0] !== null && $error[0] !== '00000') {
            throw new Exception($error[2]);
        }
        
        if ($this->lastRequestStatement === false) {
            throw new Exception(
                'An error occurred during the execution of the request'
            );
        }
        
        $this->noResult = false;
        if ($this->obtainImpactedRows() === 0) {
            $this->noResult = true;
        }

        return $this->lastRequestStatement;
    }
    
    /**
     * Closes the cursor, enabling the statement to be executed again.
     * 
     * @link http://php.net/manual/fr/pdostatement.closecursor.php
     * 
     * @return void
     */
    public function closeCursor()
    {
        return $this->lastRequestStatement->closeCursor();
    }
    
    /**
     * Return the number of impacted rows by the last request
     * 
     * @return int|bool
     */
    public function obtainImpactedRows()
    {
        if (is_object($this->lastRequestStatement)) {
            //If pdo::query or pdo::prepare
            return $this->lastRequestStatement->rowCount();
        } elseif (is_integer($this->lastRequestStatement)) {
            //If pdo::exec
            return $this->lastRequestStatement;
        }
        
        //Security if call without executed a request
        return false;
    }
    
    /**
     * To call this own request without use query writer
     * 
     * @param string $request The user request
     * 
     * @return void
     */
    public function query($request)
    {
        $this->assembledRequest = $request;
    }
    
    /**
     * Add a filter to where part of the request
     * 
     * @param string     $filter          The filter to add
     * @param array|null $preparedFilters (default: null) Filters to add
     *  in prepared request
     * 
     * @throws \Exception If key on prepared request is already used
     * 
     * @return \BfwSql\Actions\AbstractActions
     */
    public function where($filter, $preparedFilters = null)
    {
        $this->where[] = $filter;
        
        if (is_array($preparedFilters)) {
            $this->addPreparedRequestArgs($preparedFilters);
        }
        
        return $this;
    }
    
    /**
     * Add filters to prepared requests
     * 
     * @param array $preparedRequestArgs Filters to add in prepared request
     * 
     * @return void
     */
    protected function addPreparedRequestArgs(array $preparedRequestArgs)
    {
        foreach ($preparedRequestArgs as $prepareKey => $prepareValue) {
            $this->preparedRequestArgs[$prepareKey] = $prepareValue;
        }
    }
    
    /**
     * Write the where part of a sql query and return it
     * 
     * @return string
     */
    protected function generateWhere()
    {
        $where = '';
        
        //check if there are filters to write
        if (count($this->where) > 0) {
            $where = ' WHERE ';
            
            foreach ($this->where as $filter) {
                
                if ($where != ' WHERE ') {
                    $where .= ' AND ';
                }
                
                $where .= $filter;
            } 
        }
        
        return $where;
    }
    
    /**
     * Add datas to insert or update for a column.
     * Used by UPDATE and INSERT requests
     * 
     * @param array $columns Datas to add or update
     *  Format : array('sqlColumnName' => 'valueForThisColumn', ...);
     * 
     * @return \BfwSql\Actions\AbstractActions
     */
    public function addDatasForColumns(array $columns)
    {
        foreach ($columns as $columnName => $data) {
            if (
                isset($this->columns[$columnName])
                && $this->columns[$columnName] != $data
            ) {
                throw new \Exception(
                    'A different data is already declared for the column '
                    .$columnName
                );
            }
            
            $this->columns[$columnName] = $data;
        }
        
        return $this;
    }
    
    /**
     * Declare columns should be automatic quoted if value is string.
     * 
     * @param string ...$columns Columns name
     * 
     * @throws Exception If the column is already declared to be not quoted
     * 
     * @return \BfwSql\Actions\AbstractActions
     */
    public function addQuotedColumns(...$columns)
    {
        if ($this instanceof Select || $this instanceof Delete) {
            throw new Exception(
                'Sorry, automatic quoted value is not supported into '
                .get_called_class().' class'
            );
        }
        
        foreach ($columns as $columnName) {
            if (isset($this->notQuotedColumns[$columnName])) {
                throw new Exception(
                    'The column '.$columnName.' is already declared to be a'
                    .' not quoted value.'
                );
            }
            
            $this->quotedColumns[$columnName] = true;
        }
        
        return $this;
    }
    
    /**
     * Declare columns should not be automatic quoted if value is string.
     * 
     * @param string ...$columns Columns name
     * 
     * @throws Exception If the column is already declared to be quoted
     * 
     * @return \BfwSql\Actions\AbstractActions
     */
    public function addNotQuotedColumns(...$columns)
    {
        if ($this instanceof Select || $this instanceof Delete) {
            throw new Exception(
                'Sorry, automatic quoted value is not supported into '
                .get_called_class().' class'
            );
        }
        
        foreach ($columns as $columnName) {
            if (isset($this->quotedColumns[$columnName])) {
                throw new Exception(
                    'The column '.$columnName.' is already declared to be a'
                    .' quoted value.'
                );
            }
            
            $this->notQuotedColumns[$columnName] = true;
        }
        
        return $this;
    }
    
    /**
     * Quote a value if need, else return the value passed in parameter
     * 
     * @param string $columnName The column corresponding to the value
     * @param string $value      The value to quote
     * 
     * @return string
     */
    protected function quoteValue($columnName, $value)
    {
        if ($this->quoteStatus === self::QUOTE_NONE) {
            return $value;
        } elseif ($this->quoteStatus === self::QUOTE_PARTIALLY) {
            if (array_key_exists($columnName, $this->notQuotedColumns)) {
                return $value;
            }
            
            if (
                $this->partiallyPreferedMode === self::PARTIALLY_MODE_NOTQUOTE &&
                array_key_exists($columnName, $this->quotedColumns) === false
            ) {
                return $value;
            }
        }
        
        if (!is_string($value)) {
            return $value;
        }
        
        return '"'.$value.'"';
    }
    
    /**
     * Send a notify to application observers
     * 
     * @return void
     */
    protected function callObserver()
    {
        $app = \BFW\Application::getInstance();
        $app->addNotification('BfwSqlRequest', $this);
    }
}
