<?php

namespace BfwSql\Test\Helpers;

class Model extends \BfwSql\AbstractModels
{
    protected $tableName = 'model';
    
    protected $baseKeyName = 'myBase';
    
    public function setTableName($tableName)
    {
        $this->tableName = $tableName;
    }

    public function setBaseKeyName($baseKeyName)
    {
        $this->baseKeyName = $baseKeyName;
    }
}
