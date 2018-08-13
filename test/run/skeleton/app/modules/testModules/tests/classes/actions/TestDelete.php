<?php

namespace Modules\testModules\tests\classes\actions;

trait TestDelete
{
    protected function testDeleteRun()
    {
        echo 'Run TestDelete'."\n";
        
        $this->checkTest([$this, 'testDeleteExecute']);
        
        echo "\n";
    }
    
    protected function testDeleteExecute()
    {
        $date    = new \BFW\Dates;
        $dateSql = $date->getSqlFormat();
        
        $this->newTest('test BfwSql\Actions\Delete - update a line');
        $this->delete()
            ->from($this->tableName)
            ->where('id=:id', [':id' => 4])
            ->execute()
        ;
        
        $this->newTest('test BfwSql\Actions\Delete - check line deleted');
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
