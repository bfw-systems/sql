<?php

namespace Modules\testModules\tests;

trait TestSqlDelete
{
    protected function testSqlDeleteRun()
    {
        echo 'Run TestSqlDelete'."\n";
        
        $this->checkTest([$this, 'testSqlDeleteExecute']);
        
        echo "\n";
    }
    
    protected function testSqlDeleteExecute()
    {
        $date    = new \BFW\Dates;
        $dateSql = $date->getSqlFormat();
        
        $this->newTest('test BfwSql\SqlDelete - update a line');
        $this->delete($this->tableName)
            ->where('id=:id', [':id' => 4])
            ->execute();
        
        $this->newTest('test BfwSql\SqlDelete - check line deleted');
        $reqCheckLine = $this->sqlConnect->getPDO()->query(
            'SELECT `id`'
            .' FROM test_runner'
            .' WHERE id=4'
        );
        
        $resCheckLine = $reqCheckLine->fetch(\PDO::FETCH_OBJ);
        if ($resCheckLine !== false) {
            return false;
        }
        
        return true;
    }
}
