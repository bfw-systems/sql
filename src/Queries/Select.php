<?php

namespace BfwSql\Queries;

use \BfwSql\SqlConnect;

/**
 * Class to write SELECT queries
 * 
 * @package bfw-sql
 * @author Vermeulen Maxime <bulton.fr@gmail.com>
 * @version 2.0
 * 
 * @method \BfwSql\Queries\Select subQuery(string $shortcut, string|\BfwSql\Queries\AbstractQuery $subQuery)
 * @method \BfwSql\Queries\Select from(string|array $nameInfos, string|array|null $columns=null)
 * @method \BfwSql\Queries\Select order(string $expr, string|null $sort = 'ASC')
 * @method \BfwSql\Queries\Select limit([int $offset,] int $rowCount)
 * @method \BfwSql\Queries\Select group(string $expr)
 */
class Select extends AbstractQuery
{
    use JoinTrait;
    
    /**
     * {@inheritdoc}
     */
    protected $requestType = 'select';
    
    /**
     * Constructor
     * 
     * @param \BfwSql\SqlConnect $sqlConnect Instance of SGBD connexion
     * @param string             $returnType PHP type used for return result
     */
    public function __construct(SqlConnect $sqlConnect, string $returnType)
    {
        parent::__construct($sqlConnect);
        
        $usedClass      = \BfwSql\UsedClass::getInstance();
        $executerClass  = $usedClass->obtainClassNameToUse('ExecutersSelect');
        $this->executer = new $executerClass($this);
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
                $joinSql = $join->getColumns()->generate();
                
                if (empty($joinSql)) {
                    continue;
                }
                
                if ($sqlParts !== '') {
                    $sqlParts .= ',';
                }
                
                $sqlParts .= $joinSql;
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
