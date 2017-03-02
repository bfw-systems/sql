<?php

namespace BfwSql\test\unit\mocks;

class SqlActions extends \BfwSql\SqlActions
{
    public function __get($name)
    {
        return $this->{$name};
    }
    
    public function __set($name, $value)
    {
        $this->{$name} = $value;
        return $this;
    }
    
    protected function assembleRequest()
    {
        $this->assembledRequest = 'myRequest';
    }
    
    public function callExecuteQuery()
    {
        return parent::executeQuery();
    }
    
    public function callAddPreparedFilters($preparedFilters)
    {
        return parent::addPreparedFilters($preparedFilters);
    }
    
    public function callGenerateWhere()
    {
        return parent::generateWhere();
    }
    
    public function callQuoteValue($columnName, $value)
    {
        return parent::quoteValue($columnName, $value);
    }
}
