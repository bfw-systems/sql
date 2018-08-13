<?php

namespace BfwSql\Queries\Parts;

use \Exception;

class Table extends AbstractPart
{
    /**
     * @const ERR_TABLE_INFOS_BAD_FORMAT Exception if the datas which contains
     * table name and shortcut have not the expected format.
     */
    const ERR_TABLE_INFOS_BAD_FORMAT = 2515001;
    
    /**
     * @var string $name The table name
     */
    protected $name = '';
    
    /**
     * @var string|null $shortcut The table shortcut
     */
    protected $shortcut;
    
    /**
     * @var \BfwSql\Queries\Parts\ColumnList|null $columns Object containing
     * all columns of this table to use into the request
     */
    protected $columns;
    
    /**
     * @var boolean $columnsWithValue If the columnList object will contain
     * the column value or not (change the __invoke() format).
     */
    protected $columnsWithValue = false;
    
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
     * Getter accessor to property columns
     * 
     * @return \BfwSql\Queries\Parts\ColumnList|null
     */
    public function &getColumns()
    {
        return $this->columns;
    }
    
    /**
     * Getter accessor to property columnsWithValue
     * 
     * @return bool
     */
    public function getColumnsWithValue(): bool
    {
        return $this->columnsWithValue;
    }
    
    /**
     * Setter accessor to property columnsWithValue
     * 
     * @param bool $columnsWithValue If the columnList object will contain
     * the column value or not (change the __invoke() format).
     * 
     * @return $this
     */
    public function setColumnsWithValue(bool $columnsWithValue): self
    {
        $this->columnsWithValue = $columnsWithValue;
        return $this;
    }
    
    /**
     * Magic method __invoke, used when the user call object like a function
     * @link http://php.net/manual/en/language.oop5.magic.php#object.invoke
     * 
     * @param string|array $nameInfos Table name.
     *  It can be an array if a table shortcut is declared.
     *  In array mode, the format is ['shortcut' => 'name']
     * @param string|array|null $columns (default: null) Columns into this
     *  table which will be use on the final query
     * 
     * @return void
     */
    public function __invoke($nameInfos, $columns = null)
    {
        $this->defineNameAndShortcut($nameInfos);
        $this->defineColumns($columns);
    }
    
    /**
     * Find the table name and the shortcut from info passed in argument.
     * 
     * @param string|array $nameInfos Table name.
     *  It can be an array if a table shortcut is declared.
     *  In array mode, the format is ['shortcut' => 'name']
     * 
     * @return void
     * 
     * @throws \Exception If the format is not correct (string or array)
     */
    protected function defineNameAndShortcut($nameInfos)
    {
        if (!is_array($nameInfos) && !is_string($nameInfos)) {
            throw new Exception(
                'Table information is not in the right format.',
                self::ERR_TABLE_INFOS_BAD_FORMAT
            );
        }
        
        if (is_array($nameInfos)) {
            $this->name     = (string) reset($nameInfos);
            $this->shortcut = (string) key($nameInfos);
        } else {
            $this->name     = (string) $nameInfos;
            $this->shortcut = null;
        }
        
        if (!empty($this->tablePrefix)) {
            $this->name = $this->tablePrefix.$this->name;
        }
    }
    
    /**
     * Define the columns object and declare columns passed in argument
     * 
     * @param string|array|null $columns (default: null) Columns into this
     *  table which will be use on the final query
     * 
     * @return void
     */
    protected function defineColumns($columns)
    {
        $class         = $this->obtainColumnsClassName();
        $this->columns = new $class($this->querySystem, $this);
        
        if ($columns === null) {
            return;
        }
        
        if (!is_array($columns)) {
            $columns = [$columns];
        }
        
        $this->columns->__invoke($columns);
    }
    
    /**
     * Find the correct class (with namespace) to use for columns object.
     * Based on the value of the property columnWithValue.
     * 
     * @return string
     */
    protected function obtainColumnsClassName(): string
    {
        if ($this->columnsWithValue === true) {
            return __NAMESPACE__.'\ColumnValueList';
        }
        
        return __NAMESPACE__.'\ColumnList';
    }
    
    /**
     * Instanciate the columnsList object.
     * To generate it before the call to __invoke() when is necessary
     */
    public function createColumnInstance()
    {
        $this->defineColumns(null);
    }
    
    /**
     * {@inheritdoc}
     */
    public function generate(): string
    {
        $partQuery = '`'.$this->name.'`';
        
        if ($this->shortcut !== null) {
            $partQuery .= ' AS `'.$this->shortcut.'`';
        }
        
        return $partQuery;
    }
}
