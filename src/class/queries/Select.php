<?php

namespace BfwSql\Queries;

use \BfwSql\SqlConnect;

/**
 * Class to write SELECT queries
 * 
 * @package bfw-sql
 * @author Vermeulen Maxime <bulton.fr@gmail.com>
 * @version 2.0
 */
class Select extends AbstractQuery
{
    use JoinTrait;
    
    /**
     * Constructor
     * 
     * @param \BfwSql\SqlConnect $sqlConnect Instance of SGBD connexion
     * @param string             $returnType PHP type used for return result
     */
    public function __construct(SqlConnect $sqlConnect, string $returnType)
    {
        parent::__construct($sqlConnect);
        
        $this->executer = new \BfwSql\Executers\Select($this);
        $this->executer->setReturnType($returnType);
    }
    
    /**
     * {@inheritdoc}
     */
    protected function defineQueriesParts()
    {
        parent::defineQueriesParts();
        
        $this->queriesParts['subQuery']  = new Parts\SubQueryList($this);
        $this->queriesParts['from']      = $this->queriesParts['table'];
        $this->queriesParts['join']      = new Parts\JoinList($this);
        $this->queriesParts['joinLeft']  = new Parts\JoinList($this);
        $this->queriesParts['joinRight'] = new Parts\JoinList($this);
        $this->queriesParts['order']     = new Parts\OrderList($this);
        $this->queriesParts['limit']     = new Parts\Limit($this);
        $this->queriesParts['group']     = new Parts\CommonList($this);
        
        $this->joinDefinePrefix();
        $this->queriesParts['group']->setSeparator(',');
        $this->queriesParts['group']->setPartPrefix('GROUP BY');
    }
    
    /**
     * {@inheritdoc}
     */
    protected function obtainGenerateOrder(): array
    {
        return [
            'select'    => [
                'prefix'     => 'SELECT',
                'callback'   => [$this, 'generateSelect'],
                'canBeEmpty' => false
            ],
            'from'      => [
                'prefix'     => 'FROM',
                'canBeEmpty' => false
            ],
            'join'      => [],
            'joinLeft'  => [],
            'joinRight' => [],
            'where'     => [],
            'group'     => [],
            'order'     => [],
            'limit'     => []
        ];
    }
    
    /**
     * Callback used by assembleRequestPart method
     * Generate the sql query for the SELECT part who contains all columns to
     * return and all sub-queries defined.
     * 
     * @return string
     */
    protected function generateSelect(): string
    {
        $sqlParts = $this->queriesParts['from']->getColumns()->generate();
        
        $joinKeyList = ['join', 'joinLeft', 'joinRight'];
        foreach ($joinKeyList as $joinKeyName) {
            foreach ($this->queriesParts[$joinKeyName] as $join) {
                if ($sqlParts !== '') {
                    $sqlParts .= ',';
                }
                
                $sqlParts .= $join->getColumns()->generate();
            }
        }
        
        $sqlSubQueries = $this->queriesParts['subQuery']->generate();
        if ($sqlParts !== '' && $sqlSubQueries !== '') {
            $sqlParts .= ',';
        }
        
        $sqlParts .= $sqlSubQueries;
        
        return $sqlParts;
    }
} 
