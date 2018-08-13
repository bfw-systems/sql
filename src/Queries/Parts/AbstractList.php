<?php

namespace BfwSql\Queries\Parts;

use \Iterator;

abstract class AbstractList extends AbstractPart implements Iterator
{
    /**
     * @var array $list List of item into the list
     */
    protected $list = [];
    
    /**
     * @var integer $position Iterator cursor position
     */
    protected $position = 0;
    
    /**
     * @var string $separator The separator to use between items during
     * the call to generate()
     */
    protected $separator = '';
    
    /**
     * Getter accessor to property list
     * 
     * @return array
     */
    public function getList(): array
    {
        return $this->list;
    }

    /**
     * Getter accessor to property position
     * 
     * @return integer
     */
    public function getPosition(): int
    {
        return $this->position;
    }

    /**
     * Getter accessor to property separator
     * 
     * @return string
     */
    public function getSeparator(): string
    {
        return $this->separator;
    }
    
    /**
     * Setter accessor to property partPrefix
     * 
     * @param string $prefix
     * 
     * @return $this
     */
    public function setPartPrefix(string $prefix): self
    {
        $this->partPrefix = $prefix;
        return $this;
    }
    
    /**
     * {@inheritdoc}
     */
    public function current()
    {
        return $this->list[$this->position];
    }

    /**
     * {@inheritdoc}
     */
    public function key(): int
    {
        return $this->position;
    }

    /**
     * {@inheritdoc}
     */
    public function next()
    {
        ++$this->position;
    }

    /**
     * {@inheritdoc}
     */
    public function rewind()
    {
        $this->position = 0;
    }

    /**
     * {@inheritdoc}
     */
    public function valid(): bool
    {
        return isset($this->list[$this->position]);
    }
    
    /**
     * {@inheritdoc}
     */
    public function generate(): string
    {
        $sqlPart = '';
        
        foreach ($this->list as $index => $expr) {
            if ($index > 0) {
                $sqlPart .= $this->separator;
            }
            
            $sqlPart .= $expr;
        }
        
        return $sqlPart;
    }
}
