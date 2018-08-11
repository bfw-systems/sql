<?php

namespace BfwSql\Queries\Parts;

use \BfwSql\Queries\AbstractQuery;

abstract class AbstractPart implements PartInterface
{
    /**
     * @var \BfwSql\Queries\AbstractQuery $querySystem The object who generate
     * the full query
     */
    protected $querySystem;
    
    /**
     * @var string $tablePrefix The prefix to use which all table into the base
     */
    protected $tablePrefix = '';
    
    /**
     * @var string $partPrefix The prefix to use before this query part
     */
    protected $partPrefix = '';
    
    /**
     * @var boolean $usePartPrefix If the part don't have prefix
     */
    protected $usePartPrefix = true;
    
    /**
     * @var boolean $canBeEmpty If the query part generated can be empty
     * Example : The FROM part can not be empty for a SELECT query
     */
    protected $canBeEmpty = true;
    
    /**
     * Define querySystem property and find the tablePrefix
     * 
     * @param \BfwSql\Queries\AbstractQuery $querySystem
     */
    public function __construct(AbstractQuery $querySystem)
    {
        $this->querySystem = $querySystem;
        $this->tablePrefix = $querySystem
            ->getSqlConnect()
            ->getConnectionInfos()
            ->tablePrefix
        ;
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
     * Getter accessor to property tablePrefix
     * 
     * @return string
     */
    public function getTablePrefix(): string
    {
        return $this->tablePrefix;
    }

    /**
     * Getter accessor to property partPrefix
     * 
     * @return string
     */
    public function getPartPrefix(): string
    {
        return $this->partPrefix;
    }
    
    /**
     * Getter accessor to property usePartPrefix
     * 
     * @return bool
     */
    public function getUsePartPrefix(): bool
    {
        return $this->usePartPrefix;
    }
    
    /**
     * Getter accessor to property canBeEmpty
     * 
     * @return bool
     */
    public function getCanBeEmpty(): bool
    {
        return $this->canBeEmpty;
    }
}
