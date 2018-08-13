<?php

namespace BfwSql\Queries\Parts;

use \Exception;

class SubQuery
{
    /**
     * @const ERR_QUERY_NOT_OBJECT_OR_STRING Exception code if the query is
     * not an object or a string.
     */
    const ERR_QUERY_NOT_OBJECT_OR_STRING = 2513001;
    
    /**
     * @const ERR_QUERY_OBJECT_BAD_INSTANCE Exception code in the case of
     * the query is an object but with not the expected class.
     */
    const ERR_QUERY_OBJECT_BAD_INSTANCE = 2513002;
    
    /**
     * @var string $query The sub-query
     */
    protected $query = '';
    
    /**
     * @var string $shortcut The shortcut to use into the request
     */
    protected $shortcut = '';
    
    /**
     * Define the shortcut and the sql query.
     * If the sub-query is an object, call method to obtain the string query
     * 
     * @param string $shortcut The shortcut to use into the request
     * @param string|\BfwSql\Queries\AbstractQuery $query The sub-query
     */
    public function __construct(string $shortcut, $query)
    {
        $this->shortcut = $shortcut;
        $this->query    = $this->obtainAssembledQuery($query);
    }
    
    /**
     * Getter accessor to property query
     * 
     * @return string
     */
    public function getQuery(): string
    {
        return $this->query;
    }

    /**
     * Getter accessor to property shortcut
     * 
     * @return string
     */
    public function getShortcut(): string
    {
        return $this->shortcut;
    }
    
    /**
     * Obtain the sql request (string type) from the arguments
     * If it's already a string, just return the string.
     * Else, if it's an AbstractQuery, use the method assemble() to obtain
     * the string sql request.
     * 
     * @param string|\BfwSql\Queries\AbstractQuery $query The sub-query
     * 
     * @return string
     * 
     * @throws \Exception If the sub-query has not the correct type or if it's
     * not the correct class instance.
     */
    protected function obtainAssembledQuery($query): string
    {
        if (is_string($query)) {
            return $query;
        }
        
        if (!is_object($query)) {
            throw new Exception(
                'A sub-query should be a string or an AbstractQuery object',
                self::ERR_QUERY_NOT_OBJECT_OR_STRING
            );
        }
        
        if ($query instanceof \BfwSql\Queries\AbstractQuery === false) {
            throw new Exception(
                'If a sub-query is an object, '
                .'it should be an instance of AbstractQuery object',
                self::ERR_QUERY_OBJECT_BAD_INSTANCE
            );
        }
        
        return $query->assemble();
    }
    
    /**
     * Generate the sql query to use for to this sub-query
     * 
     * @return string
     */
    public function generate(): string
    {
        return '('.$this->query.') AS `'.$this->shortcut.'`';
    }
}
