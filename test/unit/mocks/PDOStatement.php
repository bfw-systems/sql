<?php

namespace BfwSql\test\unit\mocks;

class PDOStatement extends \PDOStatement
{
    protected $pdo;
    
    protected $statement = '';
    
    protected $fakeResults = [];
    
    protected $preparedArgs;
    
    public function __construct($pdo, $statement)
    {
        $this->pdo       = $pdo;
        $this->statement = $statement;
    }
    
    public function closeCursor()
    {
        reset($this->fakeResults);
    }
    
    public function errorCode()
    {
        //@TODO
    }
    
    public function errorInfo()
    {
        //@TODO
    }
    
    public function execute($bound_input_params = null)
    {
        $this->preparedArgs = $bound_input_params;
    }
    
    public function fetch(
        $fetch_style = null,
        $cursor_orientation = \PDO::FETCH_ORI_NEXT,
        $cursor_offset = 0
    ) {
        $value = current($this->fakeResults);
        next($this->fakeResults);
        
        if ($fetch_style === \PDO::FETCH_ASSOC && !is_array($value)) {
            $value = (array) $value;
        } elseif ($fetch_style === \PDO::FETCH_OBJ && !is_object($value)) {
            $value = (object) $value;
        }
        
        return $value;
    }
    
    public function rowCount()
    {
        return count($this->fakeResults);
    }
    
    public function getStatement()
    {
        return $this->statement;
    }
    
    public function setFakeResult(array $results)
    {
        $this->fakeResults = $results;
    }
    
    public function getPreparedArgs()
    {
        return $this->preparedArgs;
    }
}
