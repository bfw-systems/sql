<?php

namespace BfwSql\Queries;

use \Exception;

/**
 * Class to write UPDATE queries
 * 
 * @package bfw-sql
 * @author Vermeulen Maxime <bulton.fr@gmail.com>
 * @version 2.0
 * 
 * @method \BfwSql\Queries\Update from(string|array $nameInfos, string|array|null $columns=null)
 * @method \BfwSql\Queries\Update set(array $columns)
 */
class Update extends AbstractQuery
{
    use JoinTrait;
    
    /**
     * @const ERR_GENERATE_VALUES_NO_DATAS Exception code if no data to update.
     */
    const ERR_GENERATE_VALUES_NO_DATAS = 2406001;
    
    /**
     * @var \BfwSql\Helpers\Quoting $quoting The quoting system
     */
    protected $quoting;
    
    /**
     * {@inheritdoc}
     */
    protected $requestType = 'update';
    
    /**
     * Constructor
     * 
     * @param \BfwSql\SqlConnect $sqlConnect Instance of SGBD connexion
     * @param string $quoteStatus (default: QUOTE_ALL) Status to automatic
     *  quoted string value system.
     */
    public function __construct(
        \BfwSql\SqlConnect $sqlConnect,
        string $quoteStatus = \BfwSql\Helpers\Quoting::QUOTE_ALL
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
        
        $parts     = &$this->queriesParts; //Yes, it's just to have < 80 chars
        $partTable = $parts['table'];
        
        $usedClass     = \BfwSql\UsedClass::getInstance();
        $joinListClass = $usedClass->obtainClassNameToUse('QueriesPartsJoinList');
        
        $this->queriesParts['from']      = $partTable;
        $this->queriesParts['set']       = &$partTable->getColumns();
        $this->queriesParts['join']      = new $joinListClass($this);
        $this->queriesParts['joinLeft']  = new $joinListClass($this);
        $this->queriesParts['joinRight'] = new $joinListClass($this);
        
        $this->joinDefinePrefix();
        $this->queriesParts['from']->setColumnsWithValue(true);
        $this->queriesParts['join']->setColumnsWithValue(true);
        $this->queriesParts['joinLeft']->setColumnsWithValue(true);
        $this->queriesParts['joinRight']->setColumnsWithValue(true);
        
        $this->queriesParts['table']->createColumnInstance();
        
        $this->querySgbd->disableQueriesParts($this->queriesParts);
    }
    
    /**
     * {@inheritdoc}
     */
    protected function obtainGenerateOrder(): array
    {
        return [
            'table' => [
                'prefix'     => 'UPDATE',
                'canBeEmpty' => false,
                'callback'   => [$this, 'generateValues']
            ],
            'join' => [],
            'joinLeft' => [],
            'joinRight' => [],
            'where' => []
        ];
    }
    
    /**
     * Callback used by assembleRequestPart method
     * Generate the sql query part who contains columns list and their values
     * 
     * @return string
     * 
     * @throws \Exception
     */
    protected function generateValues(): string
    {
        $sqlSet = $this->queriesParts['table']->getColumns()->generate();
        
        $joinKeyList = ['join', 'joinLeft', 'joinRight'];
        foreach ($joinKeyList as $joinKeyName) {
            foreach ($this->queriesParts[$joinKeyName] as $join) {
                $joinSql = $join->getColumns()->generate();
                
                if (empty($joinSql)) {
                    continue;
                }
                
                if ($sqlSet !== '') {
                    $sqlSet .= ',';
                }
                
                $sqlSet .= $joinSql;
            }
        }
        
        if ($sqlSet === '') {
            throw new Exception(
                'Queries\Update : no datas to update.',
                self::ERR_GENERATE_VALUES_NO_DATAS
            );
        }
        
        return $this->queriesParts['table']->generate().' SET '.$sqlSet;
    }
}
