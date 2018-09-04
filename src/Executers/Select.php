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
     * @param bool $reexecute (default false) To reexecute the request, or just
     * have the next line.
     * 
     * @return mixed
     */
    public function fetchRow(bool $reexecute = false)
    {
        if ($this->lastRequestStatement === null || $reexecute === true) {
            $this->execute();
        }
        
        return $this->lastRequestStatement->fetch($this->obtainPdoFetchType());
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
