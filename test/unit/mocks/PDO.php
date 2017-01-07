<?php

namespace BfwSql\test\unit\mocks;

class PDO extends \PDO
{
    protected $statements = [];
    protected $calledMethod = '';
    protected $prepareOptions;
    
    public function __construct()
    {
        //Nothing to do
        //It's a mock, we not open a connexion
    }
    
    public function errorInfo()
    {
        //@TODO
    }
    
    public function quote($string, $parameter_type = \PDO::PARAM_STR)
    {
        return '\''.$string.'\'';
    }
    
    public function prepare($statement, $options = null)
    {
        $this->calledMethod   = 'prepare';
        $this->prepareOptions = $options;
        
        return $this->createStatement($statement);
    }
    
    public function exec($statement)
    {
        $this->calledMethod = 'exec';
        
        return $this->createStatement($statement);
    }
    
    public function query($statement)
    {
        $this->calledMethod = 'query';
        
        return $this->createStatement($statement);
    }
    
    protected function createStatement($statement)
    {
        $currentStatement   = new PDOStatement($this, $statement);
        $this->statements[] = $currentStatement;
        
        return $currentStatement;
    }
    
    /**
     * 
     * @return \BfwSql\test\unit\mocks\PDOStatement[]
     */
    public function getStatements()
    {
        return $this->statements;
    }
    
    /**
     * 
     * @return \BfwSql\test\unit\mocks\PDOStatement
     */
    public function getLastStatements()
    {
        return end($this->statements);
    }
    
    public function getCalledMethod()
    {
        return $this->calledMethod;
    }
    
    public function getPrepareOptions()
    {
        return $this->prepareOptions;
    }
}
