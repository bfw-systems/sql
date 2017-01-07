<?php

namespace BfwSql\test\unit\mocks;

class SqlActionsExecute extends SqlActions
{
    protected $mockExecuteQuery;
    protected $mockRowsImpacted;
    
    protected function executeQuery()
    {
        return $this->mockExecuteQuery;
    }
    
    public function obtainImpactedRows()
    {
        return $this->mockRowsImpacted;
    }
}
