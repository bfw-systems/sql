<?php

namespace BfwSql\Queries;

trait JoinTrait
{
    /**
     * Define the sql prefix to use for each join object
     * 
     * @return void
     */
    protected function joinDefinePrefix()
    {
        $this->queriesParts['join']->setPartPrefix('INNER JOIN');
        $this->queriesParts['joinLeft']->setPartPrefix('LEFT JOIN');
        $this->queriesParts['joinRight']->setPartPrefix('RIGHT JOIN');
    }
    
    /**
     * Add a INNER JOIN to the request
     * 
     * @return $this
     */
    public function join(...$args): self
    {
        array_unshift($args, 'join');
        return $this->createJoin(...$args);
    }
    
    /**
     * Add a LEFT JOIN to the request
     * 
     * @return $this
     */
    public function joinLeft(...$args): self
    {
        array_unshift($args, 'joinLeft');
        return $this->createJoin(...$args);
    }
    
    /**
     * Add a RIGHT JOIN to the request
     * 
     * @return $this
     */
    public function joinRight(...$args): self
    {
        array_unshift($args, 'joinRight');
        return $this->createJoin(...$args);
    }
    
    /**
     * Add a (inner|left|right) join to the request
     * 
     * @param string       $joinType The name of the property in this class
     *  where the join is add
     * @param string|array $table    Name of the table concerned by
     * the join. Or an array with the table shortcut in key.
     * @param string       $on       SQL part "ON" for this join
     * @param string|array $columns  Columns from the table joined to add in
     *  the SELECT part of the request
     * 
     * @return $this
     */
    protected function createJoin(
        string $joinType,
        $table,
        string $on,
        $columns = '*'
    ): self {
        $this->queriesParts[$joinType]($table, $on, $columns);
        
        return $this;
    }
}
