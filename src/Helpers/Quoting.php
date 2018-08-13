<?php

namespace BfwSql\Helpers;

use \Exception;

class Quoting
{
    /**
     * @const QUOTE_ALL To automatic quote all string values.
     * Used by SqlInsert and SqlUpdate.
     */
    const QUOTE_ALL = 'all';
    
    /**
     * @const QUOTE_ALL To not automatic quote string values.
     * Used by SqlInsert and SqlUpdate.
     */
    const QUOTE_NONE = 'none';
    
    /**
     * @const QUOTE_ALL To automatic quote string values only for somes columns
     * Used by SqlInsert and SqlUpdate.
     */
    const QUOTE_PARTIALLY = 'partially';
    
    /**
     * @const PARTIALLY_MODE_QUOTE Used by automatic quote system when is equal
     * to QUOTE_PARTIALLY. Define default quote mode to quote all columns which
     * is not define to not be quoted.
     */
    const PARTIALLY_MODE_QUOTE = 'quote';
    
    /**
     * @const PARTIALLY_MODE_NOTQUOTE Used by automatic quote system when is
     * equal to QUOTE_PARTIALLY. Define default quote mode to not quote all
     * columns which is not define to be quoted.
     */
    const PARTIALLY_MODE_NOTQUOTE = 'not quote';
    
    /**
     * @const ERR_COLUMN_ALREADY_DEFINE_NOT_QUOTED Exception code if the user
     * try to declare a column to be quoted, but the column is already declared
     * to be not quoted.
     */
    const ERR_COLUMN_ALREADY_DEFINE_NOT_QUOTED = 2701005;
    
    /**
     * @const ERR_COLUMN_ALREADY_DEFINE_QUOTED Exception code if the user try
     * to declared a column to be not quoted, but the column is already
     * declared to be quoted.
     */
    const ERR_COLUMN_ALREADY_DEFINE_QUOTED = 2701006;
    
    /**
     * @var \BfwSql\SqlConnect $sqlConnect SqlConnect object
     */
    protected $sqlConnect;
    
    /**
     * @var string $quoteStatus The current automic quote status.
     */
    protected $quoteStatus = self::QUOTE_ALL;
    
    /**
     * @var string $partiallyPreferedMode The default mode to use on column
     * when quoteStatus is declared to be PARTIALLY.
     * Value is self::PARTIALLY_MODE_QUOTE or self::PARTIALLY_MODE_NOTQUOTE
     */
    protected $partiallyPreferedMode = self::PARTIALLY_MODE_QUOTE;
    
    /**
     * @var array $quotedColumns List of columns where value will be quoted if
     * is string.
     */
    protected $quotedColumns = [];
    
    /**
     * @var array $notQuotedColumns List of columns where value will not be
     * quoted if is string.
     */
    protected $notQuotedColumns = [];
    
    /**
     * Construct
     * Define the quoting status and the sqlConnect object
     * 
     * @param string $status Globally status for the system
     *  Values are class constants :
     *  * QUOTE_ALL
     *  * QUOTE_NONE
     *  * QUOTE_PARTIALLY
     * @param \BfwSql\SqlConnect $sqlConnect The sqlConnect object
     */
    public function __construct(string $status, \BfwSql\SqlConnect $sqlConnect)
    {
        $this->quoteStatus = $status;
        $this->sqlConnect  = $sqlConnect;
    }
    
    /**
     * Getter accessor to property sqlConnect
     * 
     * @return \BfwSql\SqlConnect
     */
    public function getSqlConnect(): \BfwSql\SqlConnect
    {
        return $this->sqlConnect;
    }
    
    /**
     * Getter to access to quoteStatus property
     * 
     * @return string
     */
    public function getQuoteStatus(): string
    {
        return $this->quoteStatus;
    }
    
    /**
     * Getter to access to partiallyPreferedMode property
     * 
     * @return string
     */
    public function getPartiallyPreferedMode(): string
    {
        return $this->partiallyPreferedMode;
    }

    /**
     * Getter to access to partiallyPreferedMode property
     * Value should be self::PARTIALLY_MODE_QUOTE or
     * self::PARTIALLY_MODE_NOTQUOTE
     * 
     * @param string $partiallyPreferedMode The new prefered mode
     * 
     * @return $this
     */
    public function setPartiallyPreferedMode(string$partiallyPreferedMode): self
    {
        $this->partiallyPreferedMode = $partiallyPreferedMode;
        
        return $this;
    }
    
    /**
     * Getter to access to quotedColumns property
     * 
     * @return array
     */
    public function getQuotedColumns(): array
    {
        return $this->quotedColumns;
    }

    /**
     * Getter to access to notQuotedColumns property
     * 
     * @return array
     */
    public function getNotQuotedColumns(): array
    {
        return $this->notQuotedColumns;
    }
    
    
    /**
     * Declare columns should be automatic quoted if value is string.
     * 
     * @param string[] $columns Columns name
     * 
     * @throws Exception If the column is already declared to be not quoted
     * 
     * @return $this
     */
    public function addQuotedColumns(array $columns): self
    {
        foreach ($columns as $columnName) {
            if (isset($this->notQuotedColumns[$columnName])) {
                throw new Exception(
                    'The column '.$columnName.' is already declared to be a'
                    .' not quoted value.',
                    self::ERR_COLUMN_ALREADY_DEFINE_NOT_QUOTED
                );
            }
            
            $this->quotedColumns[$columnName] = true;
        }
        
        return $this;
    }
    
    /**
     * Declare columns should not be automatic quoted if value is string.
     * 
     * @param string[] $columns Columns name
     * 
     * @throws Exception If the column is already declared to be quoted
     * 
     * @return $this
     */
    public function addNotQuotedColumns(array $columns): self
    {
        foreach ($columns as $columnName) {
            if (isset($this->quotedColumns[$columnName])) {
                throw new Exception(
                    'The column '.$columnName.' is already declared to be a'
                    .' quoted value.',
                    self::ERR_COLUMN_ALREADY_DEFINE_QUOTED
                );
            }
            
            $this->notQuotedColumns[$columnName] = true;
        }
        
        return $this;
    }
    
    /**
     * Quote a value if need, else return the value passed in parameter
     * 
     * @param string $columnName The column corresponding to the value
     * @param mixed  $value      The value to quote
     * 
     * @return mixed
     */
    public function quoteValue(string $columnName, $value)
    {
        if ($this->quoteStatus === self::QUOTE_NONE) {
            return $value;
        } elseif ($this->quoteStatus === self::QUOTE_PARTIALLY) {
            if (array_key_exists($columnName, $this->notQuotedColumns)) {
                return $value;
            }
            
            if (
                $this->partiallyPreferedMode === self::PARTIALLY_MODE_NOTQUOTE &&
                array_key_exists($columnName, $this->quotedColumns) === false
            ) {
                return $value;
            }
        }
        
        if (!is_string($value)) {
            return $value;
        }
        
        return $this->sqlConnect->getPDO()->quote($value);
    }
}
