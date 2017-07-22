<?php

namespace Modules\testModules\tests;

trait TestSqlInsert
{
    protected function testSqlInsertRun()
    {
        echo 'Run TestSqlInsert'."\n";
        
        $this->checkTest([$this, 'testSqlInsertExecute']);
        
        echo "\n";
    }
    
    protected function testSqlInsertExecute()
    {
        $date    = new \BFW\Dates;
        $dateSql = $date->getSqlFormat();
        
        $this->newTest('test BfwSql\SqlInsert - add a line');
        $this->insert(
            $this->tableName,
            [
                'title' => 'test SqlInsert',
                'date'  => $dateSql
            ]
        )->execute();
        
        $this->newTest('test BfwSql\SqlInsert - check line inserted exist');
        $reqCheckLine = $this->sqlConnect->getPDO()->query(
            'SELECT `id`'
            .' FROM test_runner'
            .' WHERE `title`=\'test SqlInsert\' AND `date`=\''.$dateSql.'\''
        );
        
        if ($reqCheckLine->fetch() === false) {
            return false;
        }
        
        return true;
    }
}
