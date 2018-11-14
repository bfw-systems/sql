<?php

namespace BfwSql\Queries\Parts;

class Join extends Table
{
    /**
     * @var string $on The on condition used for a join
     */
    protected $on = '';
    
    /**
     * Getter accessor to property on
     * 
     * @return string
     */
    public function getOn(): string
    {
        return $this->on;
    }
    
    /**
     * Magic method __invoke, used when the user call object like a function
     * @link http://php.net/manual/en/language.oop5.magic.php#object.invoke
     * 
     * @param string|array $nameInfos The name (and shortcut) of the table
     * @param string|array|null $columns Columns of this table to use
     *  into the request
     * @param string $on The ON condition used by the join
     * 
     * @return void
     */
    public function __invoke($nameInfos, $columns = null, string $on = '')
    {
        parent::__invoke($nameInfos, $columns);
        $this->on = $on;
    }
    
    /**
     * {@inheritdoc}
     */
    public function generate(): string
    {
        return $this->querySystem
            ->getQuerySgbd()
            ->join($this->name, $this->shortcut, $this->on)
        ;
    }
}
