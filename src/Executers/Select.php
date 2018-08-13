<?php

namespace BfwSql\Executers;

use \PDO;

class Select extends Common
{
    /**
     * @var string $returnType PHP Type used for return result
     */
    protected $returnType = '';
    
    /**
     * Getter accessor to returnType property
     * 
     * @return string
     */
    public function getReturnType(): string
    {
        return $this->returnType;
    }
    
    /**
     * Setter accessor to property returnType
     * 
     * @param string $returnType The new return type value
     * 
     * @return $this
     */
    public function setReturnType(string $returnType): self
    {
        $this->returnType = $returnType;
        return $this;
    }
    
    /**
     * Return the PDO constant for the returnType declared
     * 
     * @return integer
     */
    protected function obtainPdoFetchType(): int
    {
        if ($this->returnType === 'object') {
            return PDO::FETCH_OBJ;
        }
        
        return PDO::FETCH_ASSOC;
    }
    
    /**
     * Fetch one row of the result
     * 
     * @return mixed
     */
    public function fetchRow()
    {
        $req = $this->execute();
        return $req->fetch($this->obtainPdoFetchType());
    }
    
    /**
     * Fetch all rows returned by the request
     * 
     * @return \Generator
     */
    public function fetchAll(): \Generator
    {
        $request = $this->execute(); //throw an Exception if error
        
        while ($row = $request->fetch($this->obtainPdoFetchType())) {
            yield $row;
        }
    }
}
