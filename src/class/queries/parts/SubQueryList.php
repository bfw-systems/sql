<?php

namespace BfwSql\Queries\Parts;

class SubQueryList extends AbstractList
{
    /**
     * {@inheritdoc}
     */
    protected $separator = ',';
    
    /**
     * Magic method __invoke, used when the user call object like a function
     * @link http://php.net/manual/en/language.oop5.magic.php#object.invoke
     * 
     * @param string $shortcut The shortcut to use into the request
     * @param string|\BfwSql\Queries\AbstractQuery $subQuery The sub-query
     * 
     * @return void
     */
    public function __invoke(string $shortcut, $subQuery)
    {
        $this->list[] = new SubQuery($shortcut, $subQuery);
    }
    
    /**
     * {@inheritdoc}
     */
    public function generate(): string
    {
        $sqlPart = '';
        
        foreach ($this->list as $index => $subQuery) {
            if ($index > 0) {
                $sqlPart .= $this->separator;
            }
            
            $sqlPart .= $subQuery->generate();
        }
        
        return $sqlPart;
    }
}
