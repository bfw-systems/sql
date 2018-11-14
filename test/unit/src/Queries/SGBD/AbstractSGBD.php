<?php

namespace BfwSql\Queries\SGBD\test\unit;

use \atoum;

$vendorPath = realpath(__DIR__.'/../../../../../vendor');
require_once($vendorPath.'/autoload.php');

class AbstractSGBD extends atoum
{
    use \BfwSql\Test\Helpers\CreateModule;
    
    protected $mock;
    protected $query;
    
    public function beforeTestMethod($testMethod)
    {
        $this->initModule();
        $this->createSqlConnect('myBase');
        
        $this->query = new \mock\BfwSql\Queries\AbstractQuery($this->sqlConnect);
        
        $this->mockGenerator
            ->makeVisible('obtainRequestType')
            ->makeVisible('obtainPartsToDisable')
            ->makeVisible('disableQueriesParts')
            ->generate('BfwSql\Queries\SGBD\AbstractSGBD')
        ;
        
        if ($testMethod === 'testConstruct') {
            return;
        }
        
        $this->mock = new \mock\BfwSql\Queries\SGBD\AbstractSGBD($this->query);
    }
    
    public function testConstruct()
    {
        $this->assert('test Queries\SGBD\AbstractSGBD::__construct')
            ->object($this->mock = new \mock\BfwSql\Queries\SGBD\AbstractSGBD($this->query))
                ->isInstanceOf('\BfwSql\Queries\SGBD\AbstractSGBD')
        ;
    }
    
    public function testGetQuerySystem()
    {
        $this->assert('test Queries\SGBD\AbstractSGBD::getQuerySystem')
            ->object($this->mock->getQuerySystem())
                ->isIdenticalTo($this->query)
        ;
    }
    
    public function testObtainRequestType()
    {
        $this->assert('test Queries\SGBD\AbstractSGBD::obtainRequestType')
            ->string($this->mock->obtainRequestType())
                ->isEmpty()
        ;
        
        $this->assert('test Queries\SGBD\AbstractSGBD::obtainRequestType with a select')
            ->given($query = new \mock\BfwSql\Queries\Select($this->sqlConnect, 'object'))
            ->and($mock = new \mock\BfwSql\Queries\SGBD\AbstractSGBD($query))
            ->then
            ->string($mock->obtainRequestType())
                ->isEqualTo('select')
        ;
        
        $this->assert('test Queries\SGBD\AbstractSGBD::obtainRequestType with an update')
            ->given($query = new \mock\BfwSql\Queries\Update($this->sqlConnect))
            ->and($mock = new \mock\BfwSql\Queries\SGBD\AbstractSGBD($query))
            ->then
            ->string($mock->obtainRequestType())
                ->isEqualTo('update')
        ;
    }
    
    public function testObtainPartsToDisable()
    {
        $this->assert('test Queries\SGBD\AbstractSGBD::obtainPartsToDisable')
            ->array($this->mock->obtainPartsToDisable())
                ->isEqualTo([
                    'delete' => [],
                    'insert' => [],
                    'select' => [],
                    'update' => []
                ])
        ;
    }
    
    public function testDisableQueriesParts()
    {
        $this->assert('test Queries\SGBD\AbstractSGBD::disableQueriesParts')
            ->given($queryPart = new class() {
                protected $isDisabled = false;
                
                public function getIsDisabled(): bool
                {
                    return $this->isDisabled;
                }
                
                public function setIsDisabled(bool $isDisabled)
                {
                    $this->isDisabled = $isDisabled;
                }
            })
            ->and($queriesParts = [
                'table' => new $queryPart,
                'where' => new $queryPart
            ])
            ->then
            ->variable($this->mock->disableQueriesParts($queriesParts))
                ->isNull()
            ->boolean($queriesParts['table']->getIsDisabled())
                ->isFalse()
            ->boolean($queriesParts['where']->getIsDisabled())
                ->isFalse()
        ;
    }
    
    public function testListItem()
    {
        $this->assert('test Queries\SGBD\AbstractSGBD::listItem')
            ->string($this->mock->listItem('unit', 0, ','))
                ->isEqualTo('unit')
            ->string($this->mock->listItem('test', 1, ','))
                ->isEqualTo(',test')
            ->string($this->mock->listItem('with atoum', 2, ','))
                ->isEqualTo(',with atoum')
        ;
    }
    
    public function testColumnName()
    {
        $this->assert('test Queries\SGBD\AbstractSGBD::columnName')
            ->string($this->mock->columnName('RAND()', 'table', true, false))
                ->isEqualTo('RAND()')
            ->string($this->mock->columnName('*', 'table', false, true))
                ->isEqualTo('`table`.*')
            ->string($this->mock->columnName('test', 'table', false, false))
                ->isEqualTo('`table`.`test`')
        ;
    }
    
    public function testJoin()
    {
        $this->assert('test Queries\SGBD\AbstractSGBD::join')
            ->string($this->mock->join('table', null, 'table.id=test.id'))
                ->isEqualTo('`table` ON table.id=test.id')
            ->string($this->mock->join('table', 'ta', 'ta.id=test.id'))
                ->isEqualTo('`table` AS `ta` ON ta.id=test.id')
        ;
    }
    
    public function testLimit()
    {
        $this->assert('test Queries\SGBD\AbstractSGBD::limit')
            ->string($this->mock->limit(null, 0))
                ->isEmpty()
            ->string($this->mock->limit(10, null))
                ->isEqualTo('10')
            ->string($this->mock->limit(10, 20))
                ->isEqualTo('20, 10')
        ;
    }
    
    public function testOrder()
    {
        $this->assert('test Queries\SGBD\AbstractSGBD::order')
            ->string($this->mock->order('RAND()', null, true))
                ->isEqualTo('RAND()')
            ->string($this->mock->order('id', null, false))
                ->isEqualTo('`id`')
            ->string($this->mock->order('name', 'DESC', false))
                ->isEqualTo('`name` DESC')
        ;
    }
    
    public function testSubQuery()
    {
        $this->assert('test Queries\SGBD\AbstractSGBD::subQuery')
            ->string($this->mock->subQuery('SELECT author FROM question WHERE id=42', 'author'))
                ->isEqualTo('(SELECT author FROM question WHERE id=42) AS `author`')
        ;
    }
    
    public function testTable()
    {
        $this->assert('test Queries\SGBD\AbstractSGBD::table')
            ->string($this->mock->table('table', null))
                ->isEqualTo('`table`')
            ->string($this->mock->table('table', 't'))
                ->isEqualTo('`table` AS `t`')
        ;
    }
}
