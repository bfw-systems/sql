<?php

namespace BfwSql\Test\Helpers;

class Modele extends \BfwSql\AbstractModeles
{
    protected $tableName = 'modele';
    
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
