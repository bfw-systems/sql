<?php

namespace Modules\testModules\tests\Helpers;

trait TestSecure
{
    protected function testSecureRun()
    {
        echo 'Run TestHelperSecure'."\n";
        
        $this->checkTest([$this, 'testSecureProtect']);
        
        echo "\n";
    }
    
    protected function testSecureProtect()
    {
        $dataToUpdate  = 'test SecureProtect";DELETE FROM test_runner WHERE id=1;';
        $dataProtected = \BfwSql\Helpers\Secure::protectDatas($dataToUpdate);
        
        $date    = new \BFW\Dates;
        $dateSql = $date->getSqlFormat();
        
        $this->newTest('test BfwSql\Helpers\Secure::protectDatas - update a line');
        $this->update()
            ->from(
                $this->tableName,
                [
                    'date'  => $dateSql,
                    'title' => $dataProtected
                ]
            )
            ->where('id=:id', [':id' => 2])
            ->execute()
        ;
        
        $this->newTest('test BfwSql\Helpers\Secure::protectDatas - check line id=1 always exist');
        $reqCheckLineExist = $this->sqlConnect->getPDO()->query(
            'SELECT `title`, `date`'
            .' FROM test_runner'
            .' WHERE id=1'
        );
        
        $resCheckLineExist = $reqCheckLineExist->fetch(\PDO::FETCH_OBJ);
        if ($resCheckLineExist === false) {
            return false;
        }
        
        $this->newTest('test BfwSql\Helpers\Secure::protectDatas - check line updated');
        $reqCheckLine = $this->sqlConnect->getPDO()->query(
            'SELECT `title`, `date`'
            .' FROM test_runner'
            .' WHERE id=2'
        );
        
        $resCheckLine = $reqCheckLine->fetch(\PDO::FETCH_OBJ);
        $this->newTest('test BfwSql\Helpers\Secure::protectDatas - check line updated exists');
        
        if ($resCheckLine === false) {
            return false;
        }
        
        $this->newTest('test BfwSql\Helpers\Secure::protectDatas - check title for line updated');
        if ($resCheckLine->title !== addslashes($dataToUpdate)) {
            return false;
        }
        
        $this->newTest('test BfwSql\Helpers\Secure::protectDatas - check date for line updated');
        if ($resCheckLine->date !== $dateSql) {
            return false;
        }
        
        return true;
    }
}
