<?php

namespace BfwSql\Queries\Parts;

class Column
{
    /**
     * @var \BfwSql\Queries\Parts\Table $table The table where is the column
     */
    protected $table;
    
    /**
     * @var string $name The column name
     */
    protected $name;
    
    /**
     * @var string|null $shortcut The column shortcut
     */
    protected $shortcut;
    
    /**
     * @var mixed $value The column shortcut
     */
    protected $value;
    
    /**
     * Construct
     * 
     * @param \BfwSql\Queries\Parts\Table $table The table where is the column
     * @param string $name The column name
     * @param string $shortcut The column shortcut
     * @param type $value The column shortcut
     */
    public function __construct(
        Table $table,
        string $name,
        string $shortcut = null,
        $value = null
    ) {
        $this->table    = $table;
        $this->name     = $name;
        $this->shortcut = $shortcut;
        $this->value    = $value;
    }
    
    /**
     * Getter accessor to property table
     * 
     * @return \BfwSql\Queries\Parts\Table
     */
    public function getTable(): Table
    {
        return $this->table;
    }
    
    /**
     * Getter accessor to property name
     * 
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }
    
    /**
     * Getter accessor to property shortcut
     * 
     * @return string|null
     */
    public function getShortcut()
    {
        return $this->shortcut;
    }
    
    /**
     * Getter accessor to property value
     * 
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }
    
    /**
     * Generate the sql query for to this column
     * 
     * @return string
     */
    public function generate(): string
    {
        $partQuery = $this->obtainName();
        if ($this->shortcut !== null) {
            $partQuery .= ' AS `'.$this->shortcut.'`';
        }
        
        return $partQuery;
    }
    
    /**
     * Obtain the column name to use into the sql query
     * Use shortcut if defined
     * Use backtick if needed (not a function or a keyword)
     * Add the table name before the column name
     * 
     * @return string
     */
    public function obtainName(): string
    {
        $columnName = $this->name;
        $tableName  = $this->table->getName();
        
        if ($this->table->getShortcut() !== null) {
            $tableName = $this->table->getShortcut();
        }
        
        if (
            strpos($columnName, ' ') === false &&
            strpos($columnName, '(') === false
        ) {
            //Add quote only if a column has been declared
            if ($columnName !== '*') {
                $columnName = '`'.$columnName.'`';
            }

            $columnName = '`'.$tableName.'`.'.$columnName;
        }
        
        return $columnName;
    }
    
    /**
     * Obtain the value to use into sql query
     * Return the string null if the column have the value null in php
     * Use quoting system declared into the used query system
     * 
     * @return string
     */
    public function obtainValue()
    {
        if ($this->value === null) {
            return 'null';
        }
        
        return $this
            ->table
            ->getQuerySystem()
            ->getQuoting()
            ->quoteValue($this->name, $this->value)
        ;
    }
}
