<?php

namespace BfwSql\Queries;

use \Exception;

/**
 * Class to write UPDATE queries
 * 
 * @package bfw-sql
 * @author Vermeulen Maxime <bulton.fr@gmail.com>
 * @version 2.0
 */
class Update extends AbstractQuery
{
    use JoinTrait;
    
    /**
     * @const ERR_GENERATE_VALUES_NO_DATAS Exception code if no data to update.
     */
    const ERR_GENERATE_VALUES_NO_DATAS = 2305001;
    
    /**
     * @var \BfwSql\Helpers\Quoting $quoting The quoting system
     */
    protected $quoting;
    
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
        
        $this->queriesParts['from']      = $this->queriesParts['table'];
        $this->queriesParts['join']      = new Parts\JoinList($this);
        $this->queriesParts['joinLeft']  = new Parts\JoinList($this);
        $this->queriesParts['joinRight'] = new Parts\JoinList($this);
        
        $this->joinDefinePrefix();
        $this->queriesParts['from']->setColumnsWithValue(true);
        $this->queriesParts['join']->setColumnsWithValue(true);
        $this->queriesParts['joinLeft']->setColumnsWithValue(true);
        $this->queriesParts['joinRight']->setColumnsWithValue(true);
        
        $this->queriesParts['table']->createColumnInstance();
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
                if ($sqlSet !== '') {
                    $sqlSet .= ',';
                }
                
                $sqlSet .= $join->getColumns()->generate();
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
