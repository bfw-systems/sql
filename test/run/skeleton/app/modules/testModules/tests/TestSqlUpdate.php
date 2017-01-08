<?php

namespace Modules\testModules\tests;

trait TestSqlUpdate
{
    protected function testSqlUpdateRun()
    {
        echo 'Run TestSqlUpdate'."\n";
        
        $this->checkTest([$this, 'testSqlUpdateExecute']);
        
        echo "\n";
    }
    
    protected function testSqlUpdateExecute()
    {
        $date    = new \BFW\Dates;
        $dateSql = $date->getSqlFormat();
        
        $this->newTest('test BfwSql\SqlUpdate - update a line');
        $this->update(
            $this->tableName,
            [
                'title' => '"test SqlUpdate"',
                'date'  => '"'.$dateSql.'"'
            ]
        )
        ->where('id=:id', [':id' => 2])
        ->execute();
        
        $this->newTest('test BfwSql\SqlUpdate - check line updated');
        $reqCheckLine = $this->sqlConnect->getPDO()->query(
            'SELECT `title`, `date`'
            .' FROM test_runner'
            .' WHERE id=2'
        );
        
        $resCheckLine = $reqCheckLine->fetch(\PDO::FETCH_OBJ);
        if ($resCheckLine === false) {
            return false;
        }
        
        if ($resCheckLine->title !== 'test SqlUpdate') {
            return false;
        }
        if ($resCheckLine->date !== $dateSql) {
            return false;
        }
        
        return true;
    }
}
