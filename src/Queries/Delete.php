<?php

namespace BfwSql\Queries;

/**
 * Class to write DELETE queries
 * 
 * @package bfw-sql
 * @author Vermeulen Maxime <bulton.fr@gmail.com>
 * @version 2.0
 * 
 * @method \BfwSql\Queries\Delete from(string|array $nameInfos, string|array|null $columns=null)
 */
class Delete extends AbstractQuery
{
    /**
     * {@inheritdoc}
     */
    protected $requestType = 'delete';
    
    /**
     * {@inheritdoc}
     */
    protected function defineQueriesParts()
    {
        parent::defineQueriesParts();
        
        $this->queriesParts['from'] = $this->queriesParts['table'];
        
        $this->querySgbd->disableQueriesParts($this->queriesParts);
    }
    
    /**
     * {@inheritdoc}
     */
    protected function obtainGenerateOrder(): array
    {
        return [
            'from' => [
                'prefix'     => 'DELETE FROM',
                'canBeEmpty' => false
            ],
            'where' => []
        ];
    }
}
