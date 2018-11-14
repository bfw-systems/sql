<?php

namespace BfwSql\Queries\Parts;

use \BfwSql\Queries\AbstractQuery;

class Order extends AbstractPart
{
    /**
     * @var string $expr The expression to use into the order
     */
    protected $expr;
    
    /**
     * @var string|null $sort The sort order : ASC or DESC
     */
    protected $sort;
    
    /**
     * Define the expression and the order to sort
     * 
     * @param \BfwSql\Queries\AbstractQuery $querySystem
     * @param string $expr The expression to use into the order
     * @param string|null $sort The sort order : ASC or DESC
     */
    public function __construct(
        AbstractQuery $querySystem,
        string $expr,
        $sort = 'ASC'
    ) {
        parent::__construct($querySystem);
        
        $this->expr = $expr;
        $this->sort = $sort;
    }
    
    /**
     * Getter accessor to property expr
     * 
     * @return string
     */
    public function getExpr(): string
    {
        return $this->expr;
    }
    
    /**
     * Getter accessor to property expr
     * 
     * @return string|null
     */
    public function getSort()
    {
        return $this->sort;
    }
    
    /**
     * Generate the sql query for to this order expression
     * 
     * @return string
     */
    public function generate(): string
    {
        $isFunction = false;
        if (
            strpos($this->expr, ' ') !== false ||
            strpos($this->expr, '(') !== false
        ) {
            $isFunction = true;
        }
        
        return $this->querySystem
            ->getQuerySgbd()
            ->order($this->expr, $this->sort, $isFunction)
        ;
    }
}
