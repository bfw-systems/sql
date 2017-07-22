<?php

namespace Modules\testModules\tests;

trait TestSql
{
    protected function testSqlRun()
    {
        echo 'Run TestSql'."\n";
        
        $this->checkTest([$this, 'testSqlGetLastInsertedId']);
        $this->checkTest([$this, 'testSqlGetLastInsertedIdWithoutAI']);
        $this->checkTest([$this, 'testSqlCreateId']);
        $this->checkTest([$this, 'testSqlQuery']);
        
        echo "\n";
    }
    
    protected function testSqlGetLastInsertedId()
    {
        $this->newTest('test \BfwSql\Sql::getLastInsertedId');
        
        $insertedId = $this->runExec(
            'INSERT INTO test_runner VALUES ()'
        );
        
        $sqlInsertedId = $this->getLastInsertedId();
        
        if ((int) $insertedId === (int) $sqlInsertedId) {
            return true;
        }
        
        return false;
    }
    
    protected function testSqlGetLastInsertedIdWithoutAI()
    {
        $this->newTest('test \BfwSql\Sql::getLastInsertedIdWithoutAI');
        
        $this->runExec(
            'INSERT INTO test_runner'
            .' (`title`, `date`, `enabled`)'
            .' VALUES'
            .' (\'test_unitaire\', NOW(), 1)'
        );
        $insertedId = $this->sqlConnect->getPDO()->lastInsertId();
        
        $this->runExec(
            'INSERT INTO test_runner'
            .' (`title`, `date`, `enabled`)'
            .' VALUES'
            .' (\'tests\', NOW(), 0)'
        );
        
        $sqlSearchInsertedId = $this->getLastInsertedIdWithoutAI(
            'runner',
            'id',
            'date DESC',
            'enabled=1'
        );
        
        if ((int) $insertedId === (int) $sqlSearchInsertedId) {
            return true;
        }
        
        return false;
    }
    
    protected function testSqlCreateId()
    {
        $this->runExec('TRUNCATE TABLE test_runner');
        
        $this->newTest('test \BfwSql\Sql::createId for id=1');
        if ($this->createId('runner', 'id') !== 1) {
            return false;
        }
        
        $this->newTest('test \BfwSql\Sql::createId for id=3');
        $this->runExec('DELETE FROM test_runner WHERE id=1');
        $this->runExec('INSERT INTO test_runner SET id=3');
        if ($this->createId('runner', 'id') !== 2) {
            return false;
        }
        
        $this->newTest('test \BfwSql\Sql::createId for id=5');
        $this->runExec('INSERT INTO test_runner SET id=1');
        $this->runExec('INSERT INTO test_runner SET id=2');
        $this->runExec('INSERT INTO test_runner SET id=4');
        if ($this->createId('runner', 'id') !== 5) {
            return false;
        }
        
        return true;
    }
    
    protected function testSqlQuery()
    {
        $nbQuery = $this->sqlConnect->getNbQuery();
        $reqNbId = $this->query('SELECT COUNT(id) AS nbId FROM test_runner');
        
        $this->newTest('test \BfwSql\Sql::query check upNbQuery');
        if ($this->sqlConnect->getNbQuery() !== ($nbQuery+1)) {
            return false;
        }
        
        $this->newTest('test \BfwSql\Sql::query check return');
        if (!$reqNbId instanceof \PDOStatement) {
            return false;
        }
        
        $this->newTest('test \BfwSql\Sql::query check exception');
        try {
            $this->query('SELECT COUN(id) FROM test_runner');
        } catch (\Exception $e) {
            return true;
        }
        
        return false;
    }
}
