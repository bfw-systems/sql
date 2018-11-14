<?php

namespace BfwSql\Queries\Parts;

class CommonList extends AbstractList
{
    /**
     * Setter accessor to property separator
     * Because if a common list, the separator can be different for all case.
     * 
     * @param string $separator The new separator to use
     * 
     * @return $this
     */
    public function setSeparator(string $separator): self
    {
        $this->separator = $separator;
        return $this;
    }
    
    /**
     * Magic method __invoke, used when the user call object like a function
     * @link http://php.net/manual/en/language.oop5.magic.php#object.invoke
     * 
     * @param string $expr The new expression to add into the list
     * 
     * @return void
     */
    public function __invoke(string $expr)
    {
        $this->invokeCheckIsDisabled();
        
        $this->list[] = $expr;
    }
}
