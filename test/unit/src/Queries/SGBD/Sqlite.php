<?php

namespace BfwSql\Queries\SGBD\test\unit;

use \atoum;

$vendorPath = realpath(__DIR__.'/../../../../../vendor');
require_once($vendorPath.'/autoload.php');

class Sqlite extends atoum
{
    use \BfwSql\Test\Helpers\CreateModule;
    
    protected $mock;
    protected $query;
    
    public function beforeTestMethod($testMethod)
    {
        $this->initModule();
        $this->createSqlConnect('myBase', 'sqlite');
        
        $this->query = new \mock\BfwSql\Queries\AbstractQuery($this->sqlConnect);
        
        $this->mockGenerator
            ->makeVisible('obtainRequestType')
            ->makeVisible('obtainPartsToDisable')
            ->generate('BfwSql\Queries\SGBD\Sqlite')
        ;
        
        if ($testMethod === 'testConstruct') {
            return;
        }
        
        $this->mock = new \mock\BfwSql\Queries\SGBD\Sqlite($this->query);
    }
    
    public function testObtainPartsToDisable()
    {
        $this->assert('test Queries\SGBD\Sqlite::obtainPartsToDisable')
            ->array($this->mock->obtainPartsToDisable())
                ->isEqualTo([
                    'delete' => [],
                    'insert' => [],
                    'select' => [],
                    'update' => ['join', 'joinLeft', 'joinRight']
                ])
        ;
    }
    
    public function testDisableQueriesParts()
    {
        $this->assert('test Queries\SGBD\Sqlite::disableQueriesParts with update')
            ->given($query = new \mock\BfwSql\Queries\Update($this->sqlConnect))
            ->then
            ->boolean($query->getQueriesParts()['join']->getIsDisabled())
                ->isTrue()
            ->boolean($query->getQueriesParts()['joinLeft']->getIsDisabled())
                ->isTrue()
            ->boolean($query->getQueriesParts()['joinRight']->getIsDisabled())
                ->isTrue()
            ->boolean($query->getQueriesParts()['from']->getIsDisabled())
                ->isFalse()
            ->boolean($query->getQueriesParts()['where']->getIsDisabled())
                ->isFalse()
        ;
    }
    
    public function testColumnName()
    {
        $this->assert('test Queries\SGBD\Sqlite::columnName')
            ->string($this->mock->columnName('RAND()', 'table', true, false))
                ->isEqualTo('RAND()')
            ->string($this->mock->columnName('*', 'table', false, true))
                ->isEqualTo('`table`.*')
            ->string($this->mock->columnName('test', 'table', false, false))
                ->isEqualTo('`table`.`test`')
        ;
        
        $this->assert('test Queries\SGBD\Sqlite::columnName with update')
            ->given($query = new \mock\BfwSql\Queries\Update($this->sqlConnect))
            ->and($mock = new \mock\BfwSql\Queries\SGBD\Sqlite($query))
            ->then
            ->string($mock->columnName('RAND()', 'table', true, false))
                ->isEqualTo('RAND()')
            ->string($mock->columnName('*', 'table', false, true))
                ->isEqualTo('*')
            ->string($mock->columnName('test', 'table', false, false))
                ->isEqualTo('`test`')
        ;
    }
}
