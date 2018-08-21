<?php

namespace Modules\testModules\tests\Queries;

trait TestInsert
{
    protected function testInsertRun()
    {
        echo 'Run TestInsert'."\n";
        
        $this->checkTest([$this, 'testInsertExecute']);
        
        echo "\n";
    }
    
    protected function testInsertExecute()
    {
        $date    = new \BFW\Helpers\Dates;
        $dateSql = $date->getSqlFormat();
        
        $this->newTest('test BfwSql\Actions\Insert - add a line');
        $this->insert()
            ->into(
                $this->tableName,
                [
                    'title' => 'test Insert',
                    'date'  => $dateSql
                ]
            )
            ->execute()
        ;
        
        $this->newTest('test BfwSql\Actions\Insert - check line inserted exist');
        $reqCheckLine = $this->sqlConnect->getPDO()->query(
            'SELECT `id`'
            .' FROM test_runner'
            .' WHERE `title`=\'test Insert\' AND `date`=\''.$dateSql.'\''
        );
        
        if ($reqCheckLine->fetch() === false) {
            return false;
        }
        
        return true;
    }
}
