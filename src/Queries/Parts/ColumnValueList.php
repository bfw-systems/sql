<?php

namespace BfwSql\Queries\Parts;

class ColumnValueList extends ColumnList
{
    /**
     * {@inheritdoc}
     * 
     * @param array $columns The list of columns to declare
     *  The key into the array is the name of the column.
     *  The value is the column value.
     */
    public function __invoke(array $columns)
    {
        $this->invokeCheckIsDisabled();
        
        foreach ($columns as $name => $value) {
            $this->list[] = new Column($this->table, $name, null, $value);
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
        
        $sqlPart = '';
        
        foreach ($this->list as $index => $column) {
            if ($index > 0) {
                $sqlPart .= $this->separator;
            }
            
            $sqlPart .= $column->obtainName().'='.$column->obtainValue();
        }
        
        return $sqlPart;
    }
}
