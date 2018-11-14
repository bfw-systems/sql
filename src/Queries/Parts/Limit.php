<?php

namespace BfwSql\Queries\Parts;

class Limit extends AbstractPart
{
    /**
     * {@inheritdoc}
     */
    protected $partPrefix = 'LIMIT';
    
    /**
     * @var integer|null $rowCount The maximum number of rows to return
     */
    protected $rowCount = null;
    
    /**
     * @var integer|null $offset The offset of the first row to return
     */
    protected $offset = null;
    
    /**
     * Getter accessor to property rowCount
     * 
     * @return integer|null
     */
    public function getRowCount()
    {
        return $this->rowCount;
    }

    /**
     * Getter accessor to property offset
     * 
     * @return integer|null
     */
    public function getOffset()
    {
        return $this->offset;
    }
    
    /**
     * Magic method __invoke, used when the user call object like a function
     * @link http://php.net/manual/en/language.oop5.magic.php#object.invoke
     * 
     * @param array $limitInfos If one args, the number of row to return.
     *  If two args, the first is the offset, the second is the number
     *  of row to return.
     */
    public function __invoke(...$limitInfos)
    {
        $this->invokeCheckIsDisabled();
        
        if (isset($limitInfos[1])) {
            $this->offset   = (int) $limitInfos[0];
            $this->rowCount = (int) $limitInfos[1];
        } else {
            $this->rowCount = (int) $limitInfos[0];
        }
    }
    
    /**
     * {@inheritdoc}
     */
    public function generate(): string
    {
        if ($this->isDisabled === true) {
            return '';
        }
        
        if ($this->rowCount === null) {
            return '';
        } else if ($this->offset === null) {
            return (string) $this->rowCount;
        } else {
            return $this->offset.', '.$this->rowCount;
        }
    }
}
