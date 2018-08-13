<?php

namespace Modules\testModules\tests\classes\actions;

trait TestUpdate
{
    protected function testUpdateRun()
    {
        echo 'Run TestUpdate'."\n";
        
        $this->checkTest([$this, 'testUpdateExecute']);
        
        echo "\n";
    }
    
    protected function testUpdateExecute()
    {
        $date    = new \BFW\Dates;
        $dateSql = $date->getSqlFormat();
        
        $this->newTest('test BfwSql\Actions\Update - update a line');
        $this->update()
            ->from(
                $this->tableName,
                [
                    'title' => 'test Update',
                    'date'  => $dateSql
                ]
            )
            ->where('id=:id', [':id' => 2])
            ->execute()
        ;
        
        $this->newTest('test BfwSql\Actions\Update - check line updated');
        $reqCheckLine = $this->sqlConnect->getPDO()->query(
            'SELECT `title`, `date`'
            .' FROM test_runner'
            .' WHERE id=2'
        );
        
        $resCheckLine = $reqCheckLine->fetch(\PDO::FETCH_OBJ);
        if ($resCheckLine === false) {
            return false;
        }
        
        if ($resCheckLine->title !== 'test Update') {
            return false;
        }
        if ($resCheckLine->date !== $dateSql) {
            return false;
        }
        
        return true;
    }
}
