<?php

namespace BfwSql\Queries;

/**
 * Class to write INSERT INTO queries
 * 
 * @package bfw-sql
 * @author Vermeulen Maxime <bulton.fr@gmail.com>
 * @version 2.0
 * 
 * @method \BfwSql\Queries\Insert into(string|array $nameInfos, string|array|null $columns=null)
 * @method \BfwSql\Queries\Insert values(array $columns)
 * @method \BfwSql\Queries\Select select()
 * @method \BfwSql\Queries\Insert onDuplicate(array $columns)
 */
class Insert extends AbstractQuery
{
    /**
     * @var \BfwSql\Helpers\Quoting $quoting The quoting system
     */
    protected $quoting;
    
    /**
     * {@inheritdoc}
     */
    protected $requestType = 'insert';
    
    /**
     * Constructor
     * 
     * @param \BfwSql\SqlConnect $sqlConnect Instance of SGBD connexion
     * @param string $quoteStatus (default: QUOTE_ALL) Status to automatic
     *  quoted string value system.
     */
    public function __construct(
        \BfwSql\SqlConnect $sqlConnect,
        $quoteStatus = \BfwSql\Helpers\Quoting::QUOTE_ALL
    ) {
        parent::__construct($sqlConnect);
        
        $this->quoting = new \BfwSql\Helpers\Quoting(
            $quoteStatus,
            $this->sqlConnect
        );
    }
    
    /**
     * Getter accessor to property quoting
     * 
     * @return \BfwSql\Helpers\Quoting
     */
    public function getQuoting(): \BfwSql\Helpers\Quoting
    {
        return $this->quoting;
    }
    
    /**
     * {@inheritdoc}
     */
    protected function defineQueriesParts()
    {
        parent::defineQueriesParts();
        unset($this->queriesParts['where']);
        
        $parts     = &$this->queriesParts; //Yes, it's just to have < 80 chars
        $partTable = $parts['table'];
        $partTable->setColumnsWithValue(true);
        $partTable->createColumnInstance();
        
        $usedClass       = \BfwSql\UsedClass::getInstance();
        $selectClass     = $usedClass->obtainClassNameToUse('QueriesSelect');
        $colValListClass = $usedClass->obtainClassNameToUse('QueriesPartsColumnValueList');
        
        $parts['into']        = $partTable;
        $parts['values']      = &$partTable->getColumns();
        $parts['select']      = new $selectClass($this->sqlConnect, 'object');
        $parts['onDuplicate'] = new $colValListClass($this, $partTable);
        
        $this->querySgbd->disableQueriesParts($this->queriesParts);
    }
    
    /**
     * {@inheritdoc}
     */
    protected function obtainGenerateOrder(): array
    {
        return [
            'table'           => [
                'prefix'     => 'INSERT INTO',
                'canBeEmpty' => false
            ],
            'values'          => [
                'callback'      => [$this, 'generateValues'],
                'usePartPrefix' => false
            ],
            'select'          => [
                'callback'      => [$this, 'generateSelect'],
                'usePartPrefix' => false
            ],
            'onDuplicate' => [
                'prefix'   => 'ON DUPLICATE KEY UPDATE',
                'callback' => [$this, 'generateOnDuplicate']
            ]
        ];
    }
    
    /**
     * Callback used by assembleRequestPart method
     * Generate the sql query part who contains columns list and their values
     * 
     * @return string
     */
    protected function generateValues(): string
    {
        $listNames   = '';
        $listValues  = '';
        
        foreach ($this->queriesParts['values'] as $colIndex => $column) {
            
            if ($colIndex > 0) {
                $listNames  .= ',';
                $listValues .= ',';
            }
            
            $listNames  .= '`'.$column->getName().'`';
            $listValues .= $column->obtainValue();
        }
        
        if ($listNames === '') {
            return '';
        }
        
        $select = $this->queriesParts['select'];
        if ($select->getQueriesParts()['table']->getName() === '') {
            return '('.$listNames.') VALUES ('.$listValues.')';
        } else {
            return '('.$listNames.')';
        }
    }
    
    /**
     * Callback used by assembleRequestPart method
     * Generate the sql query for the SELECT part
     * 
     * @return string
     */
    protected function generateSelect(): string
    {
        $select = $this->queriesParts['select'];
        if ($select->getQueriesParts()['table']->getName() === '') {
            return '';
        }
        
        return $select->assemble();
    }
    
    /**
     * Callback used by assembleRequestPart method
     * Generate the sql query for the ON DUPLICATE KEY UPDATE part
     * 
     * @return string
     */
    protected function generateOnDuplicate(): string
    {
        $listColumns = $this->queriesParts['onDuplicate'];
        $sqlPart     = '';
        
        foreach ($listColumns as $colIndex => $column) {
            if ($colIndex > 0) {
                $sqlPart .= ',';
            }
            
            $sqlPart .= '`'.$column->getName().'`='.$column->obtainValue();
        }
        
        return $sqlPart;
    }
} 
