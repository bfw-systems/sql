<?php

namespace BfwSql\Executers\test\unit;

use \atoum;

$vendorPath = realpath(__DIR__.'/../../../../../vendor');
require_once($vendorPath.'/autoload.php');
require_once($vendorPath.'/bulton-fr/bfw/test/unit/helpers/ObserverArray.php');

class Common extends atoum
{
    use \BfwSql\Test\Helpers\CreateModule;
    
    protected $mock;
    protected $query;
    
    public function beforeTestMethod($testMethod)
    {
        $this->initModule();
        $this->createSqlConnect('myBase');
        
        $this->mockGenerator
            ->makeVisible('executeQuery')
            ->makeVisible('executePreparedQuery')
            ->makeVisible('executeNotPreparedQuery')
            ->makeVisible('callObserver')
            ->generate('BfwSql\Executers\Common')
        ;
        
        $this->defineQuery();
        
        if ($testMethod === 'testConstructAndGetters') {
            return;
        }
        
        $this->mock = new \mock\BfwSql\Executers\Common($this->query);
    }
    
    protected function defineQuery()
    {
        $this->query = new class($this->sqlConnect) extends \BfwSql\Queries\AbstractQuery {
            protected function obtainGenerateOrder() :array
            {
                return [];
            }
            
            public function setAssembledRequest(string $query)
            {
                $this->assembledRequest = $query;
            }
        };
    }
    
    public function testConstructAndGetters()
    {
        $this->assert('test Executers\Common::__construct')
            ->object($this->mock = new \mock\BfwSql\Executers\Common($this->query))
                ->isInstanceOf('\BfwSql\Executers\Common')
            ->object($this->mock->getQuery())
                ->isIdenticalTo($this->query)
            ->object($this->mock->getSqlConnect())
                ->isIdenticalTo($this->sqlConnect)
        ;
    }
    
    public function testGetAndSetIsPreparedRequest()
    {
        $this->assert('test Executers\Common::getIsPreparedRequest for default value')
            ->boolean($this->mock->getIsPreparedRequest())
                ->isTrue()
        ;
        
        $this->assert('test Executers\Common::setIsPreparedRequest with bool value')
            ->object($this->mock->setIsPreparedRequest(false))
                ->isIdenticalTo($this->mock)
            ->boolean($this->mock->getIsPreparedRequest())
                ->isFalse()
        ;
        
        $this->assert('test Executers\Common::setIsPreparedRequest with non bool value')
            ->object($this->mock->setIsPreparedRequest(1))
                ->isIdenticalTo($this->mock)
            ->boolean($this->mock->getIsPreparedRequest())
                ->isTrue()
        ;
    }
    
    public function testGetAndSetPrepareDriversOptions()
    {
        $this->assert('test Executers\Common::getPrepareDriversOptions for default value')
            ->array($this->mock->getPrepareDriversOptions())
                ->isEmpty()
        ;
        
        $this->assert('test Executers\Common::setPrepareDriversOptions')
            ->object($this->mock->setPrepareDriversOptions([
                \PDO::ATTR_CURSOR => \PDO::CURSOR_FWDONLY
            ]))
                ->isIdenticalTo($this->mock)
            ->array($this->mock->getPrepareDriversOptions())
                ->isEqualTo([
                    \PDO::ATTR_CURSOR => \PDO::CURSOR_FWDONLY
                ])
        ;
    }
    
    public function testGetNoResult()
    {
        $this->assert('test Executers\Common::getNoResult for default value')
            ->boolean($this->mock->getNoResult())
                ->isFalse()
        ;
    }
    
    public function testGetLastRequestStatement()
    {
        $this->assert('test Executers\Common::getLastRequestStatement for default value')
            ->variable($this->mock->getLastRequestStatement())
                ->isNull()
        ;
    }
    
    public function testGetLastErrorInfos()
    {
        $this->assert('test Executers\Common::getLastErrorInfos for default value')
            ->array($this->mock->getLastErrorInfos())
                ->isEmpty()
        ;
    }
    
    public function testExecutePreparedQuery()
    {
        $this->assert('test Executers\Common::executePreparedQuery - prepare')
            ->given($statement = new \mock\PDOStatement)
            ->if($this->calling($this->pdo)->prepare = $statement)
            ->and($this->calling($statement)->execute = null)
            ->then
        ;
        
        $this->assert('test Executers\Common::executePreparedQuery with not prepared param and driver option')
            ->if($this->query->setAssembledRequest('SELECT id FROM test'))
            ->then
            
            ->object($this->mock->executePreparedQuery())
                ->isIdenticalTo($statement)
            ->mock($this->pdo)
                ->call('prepare')
                    ->withArguments('SELECT id FROM test', [])
                        ->once()
            ->mock($statement)
                ->call('execute')
                    ->withArguments([])
                        ->once()
        ;
        
        $this->assert('test Executers\Common::executePreparedQuery with prepared param and driver option')
            ->if($this->query->setAssembledRequest('SELECT id FROM test WHERE name=:name'))
            ->and($this->query->addPreparedParams([':name' => 'atoum']))
            ->and($this->mock->setPrepareDriversOptions([\PDO::ATTR_CURSOR => \PDO::CURSOR_FWDONLY]))
            ->then
            
            ->object($this->mock->executePreparedQuery())
                ->isIdenticalTo($statement)
            ->mock($this->pdo)
                ->call('prepare')
                    ->withArguments(
                        'SELECT id FROM test WHERE name=:name',
                        [\PDO::ATTR_CURSOR => \PDO::CURSOR_FWDONLY]
                    )
                        ->once()
            ->mock($statement)
                ->call('execute')
                    ->withArguments([':name' => 'atoum'])
                        ->once()
        ;
    }
    
    public function testExecuteNotPreparedQuery()
    {
        $this->assert('test Executers\Common::executeNotPreparedQuery - prepare')
            ->if($this->mock->setIsPreparedRequest(false))
            ->then
            
            ->given($statement = new \mock\PDOStatement)
            ->if($this->calling($this->pdo)->exec = $statement)
            ->if($this->calling($this->pdo)->query = $statement)
            ->then
            
            ->if($this->query->setAssembledRequest('INSERT INTO test'))
        ;
        
        $this->assert('test Executers\Common::executeNotPreparedQuery with exec')
            ->object($this->mock->executeNotPreparedQuery())
                ->isIdenticalTo($statement)
            ->mock($this->pdo)
                ->call('exec')
                    ->withArguments('INSERT INTO test')
                        ->once()
                ->call('query')
                    ->never()
        ;
        
        $this->assert('test Executers\Common::executeNotPreparedQuery with query')
            ->if($query = new \BfwSql\Queries\Select($this->sqlConnect, 'object'))
            ->and($query->from('myTable', 'id'))
            ->then
            ->given($setQuery = function($query) {
                $this->query = $query;
            })
            ->and($setQuery->call($this->mock, $query))
            ->and($query->assemble())
            ->then
            
            ->object($this->mock->executeNotPreparedQuery())
                ->isIdenticalTo($statement)
            ->mock($this->pdo)
                ->call('query')
                    ->withArguments(
                        'SELECT `test_myTable`.`id`'."\n"
                        .'FROM `test_myTable`'."\n"
                    )
                        ->once()
                ->call('exec')
                    ->never()
        ;
    }
    
    public function testExecuteQueryWithPreparedRequest()
    {
        $this->assert('test Executers\Common::executeQuery with prepared request - prepare')
            ->given($statement = new \mock\PDOStatement)
            ->if($this->calling($this->mock)->executePreparedQuery = $statement)
            ->if($this->calling($this->mock)->executeNotPreparedQuery = null)
            ->and($this->calling($this->pdo)->errorInfo = function() {
                //From http://php.net/manual/en/pdostatement.errorinfo.php
                return [
                    0 => '00000',
                    1 => null,
                    2 => null
                ];
            })
            ->then
            
            ->if($this->query->setAssembledRequest('SELECT id FROM test'))
            ->and($this->calling($this->mock)->callObserver = null)
        ;
        
        $this->assert('test Executers\Common::executeQuery with prepared request')
            ->array($this->mock->executeQuery())
                ->isEqualTo([
                    0 => '00000',
                    1 => null,
                    2 => null
                ])
            ->integer($this->sqlConnect->getNbQuery())
                ->isEqualTo(1)
            ->object($this->mock->getLastRequestStatement())
                ->isIdenticalTo($statement)
            ->array($this->mock->getLastErrorInfos())
                ->isEqualTo([
                    0 => '00000',
                    1 => null,
                    2 => null
                ])
            ->mock($this->mock)
                ->call('executePreparedQuery')
                    ->once()
                ->call('executeNotPreparedQuery')
                    ->never()
                ->call('callObserver')
                    ->once()
        ;
    }
    
    public function testExecuteQueryWithoutPreparedRequest()
    {
        $this->assert('test Executers\Common::executeQuery without prepared request - prepare')
            ->if($this->mock->setIsPreparedRequest(false))
            ->then
            
            ->given($statement = new \mock\PDOStatement)
            ->if($this->calling($this->mock)->executePreparedQuery = null)
            ->if($this->calling($this->mock)->executeNotPreparedQuery = $statement)
            ->and($this->calling($this->pdo)->errorInfo = function() {
                //From http://php.net/manual/en/pdostatement.errorinfo.php
                return [
                    0 => '00000',
                    1 => null,
                    2 => null
                ];
            })
            ->then
            
            ->if($this->query->setAssembledRequest('SELECT id FROM test'))
            ->and($this->calling($this->mock)->callObserver = null)
        ;
        
        $this->assert('test Executers\Common::executeQuery without prepared request')
            ->array($this->mock->executeQuery())
                ->isEqualTo([
                    0 => '00000',
                    1 => null,
                    2 => null
                ])
            ->integer($this->sqlConnect->getNbQuery())
                ->isEqualTo(1)
            ->object($this->mock->getLastRequestStatement())
                ->isIdenticalTo($statement)
            ->array($this->mock->getLastErrorInfos())
                ->isEqualTo([
                    0 => '00000',
                    1 => null,
                    2 => null
                ])
            ->mock($this->mock)
                ->call('executePreparedQuery')
                    ->never()
                ->call('executeNotPreparedQuery')
                    ->once()
                ->call('callObserver')
                    ->once()
        ;
    }
    
    public function testExecute()
    {
        $this->assert('test Executers\Common::execute - prepare')
            ->given($setLastRequestStatement = function ($lastRequestStatement) {
                $this->lastRequestStatement = $lastRequestStatement;
            })
        ;
            
        $this->assert('test Executers\Common::execute when the execution have an error')
            ->if($this->calling($this->mock)->executeQuery = function() {
                //From http://php.net/manual/en/pdostatement.errorinfo.php
                return [
                    0 => '42S02',
                    1 => -204,
                    2 => '[IBM][CLI Driver][DB2/LINUX] SQL0204N  "DANIELS.BONES" is an undefined name.  SQLSTATE=42704'
                ];
            })
            ->then
            
            ->exception(function() {
                $this->mock->execute();
            })
                ->hasCode(\BfwSql\Executers\Common::ERR_EXECUTE_BAD_REQUEST)
        ;
        
        $this->assert('test Executers\Common::execute when the execution have an not detected error')
            ->if($this->calling($this->mock)->executeQuery = function() {
                return [
                    0 => '00000',
                    1 => null,
                    2 => null
                ];
            })
            ->and($setLastRequestStatement->call($this->mock, false))
            ->then
            
            ->exception(function() {
                $this->mock->execute();
            })
                ->hasCode(\BfwSql\Executers\Common::ERR_EXECUTED_UNKNOWN_ERROR)
        ;
        
        $this->assert('test Executers\Common::execute without error but no row impacted')
            ->given($statement = new \mock\PDOStatement)
            ->if($this->calling($this->mock)->executeQuery = function() {
                return [
                    0 => '00000',
                    1 => null,
                    2 => null
                ];
            })
            ->and($setLastRequestStatement->call($this->mock, $statement))
            ->and($this->calling($this->mock)->obtainImpactedRows = 0)
            ->then
            
            ->object($this->mock->execute())
                ->isIdenticalTo($statement)
            ->boolean($this->mock->getNoResult())
                ->isTrue()
        ;
        
        $this->assert('test Executers\Common::execute without error and with many rows impacted')
            ->given($statement = new \mock\PDOStatement)
            ->if($this->calling($this->mock)->executeQuery = function() {
                return [
                    0 => '00000',
                    1 => null,
                    2 => null
                ];
            })
            ->and($setLastRequestStatement->call($this->mock, $statement))
            ->and($this->calling($this->mock)->obtainImpactedRows = 10)
            ->then
            
            ->object($this->mock->execute())
                ->isIdenticalTo($statement)
            ->boolean($this->mock->getNoResult())
                ->isFalse()
        ;
    }
    
    public function testCloseCursor()
    {
        $this->assert('test Executers\Common::closeCursor - prepare')
            ->given($setLastRequestStatement = function ($lastRequestStatement) {
                $this->lastRequestStatement = $lastRequestStatement;
            })
        ;
            
        $this->assert('test Executers\Common::closeCursor')
            ->given($statement = new \mock\PDOStatement)
            ->if($this->calling($statement)->closeCursor = null)
            ->and($setLastRequestStatement->call($this->mock, $statement))
            ->then
            
            ->variable($this->mock->closeCursor())
                ->isNull() //Because mock
            ->mock($statement)
                ->call('closeCursor')
                    ->once()
        ;
    }
    
    public function testObtainImpactedRows()
    {
        $this->assert('test Executers\Common::obtainImpactedRows - prepare')
            ->given($setLastRequestStatement = function ($lastRequestStatement) {
                $this->lastRequestStatement = $lastRequestStatement;
            })
        ;
            
        $this->assert('test Executers\Common::obtainImpactedRows if no request have been executed')
            ->boolean($this->mock->obtainImpactedRows())
                ->isFalse()
        ;
        
        $this->assert('test Executers\Common::obtainImpactedRows if last statement is an object')
            ->given($statement = new \mock\PDOStatement)
            ->if($this->calling($statement)->rowCount = 42)
            ->and($setLastRequestStatement->call($this->mock, $statement))
            ->then
            ->integer($this->mock->obtainImpactedRows())
                ->isEqualTo(42)
            ->mock($statement)
                ->call('rowCount')
                    ->once()
        ;
        
        $this->assert('test Executers\Common::obtainImpactedRows if last statement is an integer')
            ->if($setLastRequestStatement->call($this->mock, 10))
            ->then
            ->integer($this->mock->obtainImpactedRows())
                ->isEqualTo(10)
        ;
    }
    
    public function testCallObserver()
    {
        $this->assert('test Executers\Common::callObserver')
            ->given($observer = new \BFW\Test\Helpers\ObserverArray)
            ->given($subjectList = $this->app->getSubjectList())
            ->given($sqlSubject = $subjectList->getSubjectByName('bfw-sql'))
            ->given($sqlSubject->attach($observer))
            ->then
            
            ->variable($this->mock->callObserver())
                ->isNull()
            ->array($observer->getUpdateReceived())
                ->isNotEmpty()
            ->string($observer->getUpdateReceived()[0]->action)
                ->isEqualTo('system query')
            ->object($observer->getUpdateReceived()[0]->context)
                ->isEqualTo($this->mock)
        ;
    }
}