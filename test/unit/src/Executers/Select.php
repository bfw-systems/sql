<?php

namespace BfwSql\Executers\test\unit;

use \atoum;

$vendorPath = realpath(__DIR__.'/../../../../vendor');
require_once($vendorPath.'/autoload.php');

class Select extends atoum
{
    use \BfwSql\Test\Helpers\CreateModule;
    
    protected $mock;
    protected $query;
    
    public function beforeTestMethod($testMethod)
    {
        $this->initModule();
        $this->createSqlConnect('myBase');
        
        $this->mockGenerator
            ->makeVisible('obtainPdoFetchType')
            ->generate('BfwSql\Executers\Select')
        ;
        
        $this->query = new \BfwSql\Queries\Select($this->sqlConnect, 'object');
        $this->mock  = new \mock\BfwSql\Executers\Select($this->query);
        
        $setExecuter = function($executer) {
            $this->executer = $executer;
        };
        $setExecuter->call($this->query, $this->mock);
        
        if ($testMethod !== 'testGetAndSetReturnType') {
            $this->mock->setReturnType('object');
        }
    }
    
    public function testGetAndSetReturnType()
    {
        $this->assert('test Executers\Select::getReturnType and setReturnType')
            ->string($this->mock->getReturnType())
                ->isEmpty()
            ->object($this->mock->setReturnType('object'))
                ->isIdenticalTo($this->mock)
            ->string($this->mock->getReturnType())
                ->isEqualTo('object')
        ;
    }
    
    public function testObtainPdoFetchType()
    {
        $this->assert('test Executers\Select::obtainPdoFetchType with object type')
            ->if($this->mock->setReturnType('object'))
            ->then
            ->variable($this->mock->obtainPdoFetchType())
                ->isEqualTo(\PDO::FETCH_OBJ)
        ;
        
        $this->assert('test Executers\Select::obtainPdoFetchType with array type')
            ->if($this->mock->setReturnType('array'))
            ->then
            ->variable($this->mock->obtainPdoFetchType())
                ->isEqualTo(\PDO::FETCH_ASSOC)
        ;
        
        $this->assert('test Executers\Select::obtainPdoFetchType with bad value')
            ->if($this->mock->setReturnType(42))
            ->then
            ->variable($this->mock->obtainPdoFetchType())
                ->isEqualTo(\PDO::FETCH_ASSOC)
        ;
    }
    
    public function testFetchRow()
    {
        $this->assert('test Executers\Select::fetchRow - prepare')
            ->given($mock = $this->mock)
            ->given($pdoStatement = new \mock\PDOStatement)
            ->given($fetchReturn = (object) [
                'type'    => 'unit_test',
                'libName' => 'atoum'
            ])
            ->given($setLastRequestStatement = function ($lastRequestStatement) {
                $this->lastRequestStatement = $lastRequestStatement;
            })
            ->then
            
            ->if($this->calling($pdoStatement)->fetch = $fetchReturn)
            ->and($this->calling($this->mock)->execute = function() use ($mock, $pdoStatement, $setLastRequestStatement) {
                $setLastRequestStatement->call($mock, $pdoStatement);
                return $pdoStatement;
            })
            ->then
        ;
        
        $this->assert('test Executers\Select::fetchRow for first call')
            ->object($this->mock->fetchRow())
                ->isIdenticalTo($fetchReturn)
            ->mock($this->mock)
                ->call('execute')
                    ->once()
        ;
        
        $this->assert('test Executers\Select::fetchRow for next call')
            ->object($this->mock->fetchRow())
            ->mock($this->mock)
                ->call('execute')
                    ->never()
        ;
        
        $this->assert('test Executers\Select::fetchRow with reexecute')
            ->object($this->mock->fetchRow(true))
            ->mock($this->mock)
                ->call('execute')
                    ->once()
        ;
    }
    
    public function testFetchAll()
    {
        $this->assert('test Executers\Select::fetchAll')
            ->given($pdoStatement = new \mock\PDOStatement)
            ->given($fetchReturn = [
                1 => (object) [
                    'type'    => 'unit_test',
                    'libName' => 'atoum'
                ],
                2 => (object) [
                    'type'    => 'unit_test',
                    'libName' => 'phpunit'
                ],
            ])
            ->then
            
            ->if($this->calling($pdoStatement)->fetch[1] = $fetchReturn[1])
            ->and($this->calling($pdoStatement)->fetch[2] = $fetchReturn[2])
            ->and($this->calling($pdoStatement)->fetch[3] = false)
            ->and($this->calling($this->mock)->execute = $pdoStatement)
            ->then
            
            ->generator($this->mock->fetchAll())
                ->yields->object->isIdenticalTo($fetchReturn[1])
                ->yields->object->isIdenticalTo($fetchReturn[2])
        ;
    }
}