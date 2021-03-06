<?php

namespace BfwSql\Queries\Parts;

class OrderList extends AbstractList
{
    /**
     * {@inheritdoc}
     */
    protected $partPrefix = 'ORDER BY';
    
    /**
     * {@inheritdoc}
     */
    protected $separator = ',';
    
    /**
     * Magic method __invoke, used when the user call object like a function
     * @link http://php.net/manual/en/language.oop5.magic.php#object.invoke
     * 
     * @param string $expr The expression to use into the order
     * @param string|null $sort The sort order : ASC or DESC
     * 
     * @return void
     */
    public function __invoke(string $expr, $sort = 'ASC')
    {
        $this->invokeCheckIsDisabled();
        
        $usedClass   = \BfwSql\UsedClass::getInstance();
        $orderClass = $usedClass->obtainClassNameToUse('QueriesPartsOrder');
        
        $this->list[] = new $orderClass($this->querySystem, $expr, $sort);
    }
    
    /**
     * {@inheritdoc}
     */
    public function generate(): string
    {
        if ($this->isDisabled === true) {
            return '';
        }
        
        $sqlPart = '';
        
        foreach ($this->list as $index => $order) {
            $sqlPart .= $this->querySystem->getQuerySgbd()->listItem(
                $order->generate(),
                $index,
                $this->separator
            );
        }
        
        return $sqlPart;
    }
}
