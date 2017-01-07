<?php

namespace BfwSql\test\unit\mocks;

class SqlSelect extends \BfwSql\SqlSelect
{
    use SqlActionsItems;
    
    public function setReturnType($returnType)
    {
        $this->returnType = $returnType;
    }
    
    public function callObtainTableInfos($table)
    {
        return parent::obtainTableInfos($table);
    }
    
    public function callAddColumnsForSelect($columns, $tableName)
    {
        return parent::addColumnsForSelect($columns, $tableName);
    }
    
    public function callObtainPdoFetchType()
    {
        return parent::obtainPdoFetchType();
    }
    
    public function callGenerateSelect()
    {
        return parent::generateSelect();
    }
    
    public function callGenerateFrom()
    {
        return parent::generateFrom();
    }
    
    public function callGenerateJoin($joinProperty)
    {
        return parent::generateJoin($joinProperty);
    }
    
    public function callGenerateOrderBy()
    {
        return parent::generateOrderBy();
    }
    
    public function callGenerateGroupBy()
    {
        return parent::generateGroupBy();
    }
    
    public function callGenerateLimit()
    {
        return parent::generateLimit();
    }
}
