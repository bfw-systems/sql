<?php

namespace BfwSql\Queries\Parts\test\unit;

use \Atoum;

$vendorPath = realpath(__DIR__.'/../../../../../../vendor');
require_once($vendorPath.'/autoload.php');

class Table extends atoum
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
            ->makeVisible('defineNameAndShortcut')
            ->makeVisible('defineColumns')
            ->makeVisible('obtainColumnsClassName')
            ->generate('BfwSql\Queries\Parts\Table')
        ;
        
        if ($testMethod === 'testConstructAndGetters') {
            return;
        }
        
        $this->mock = new \mock\BfwSql\Queries\Parts\Table($this->query);
    }
    
    public function testConstructAndGetters()
    {
        $this->assert('test Queries\Parts\Table::__construct')
            ->object($this->mock = new \mock\BfwSql\Queries\Parts\Table($this->query))
                ->isInstanceOf('\BfwSql\Queries\Parts\Table')
                ->isInstanceOf('\BfwSql\Queries\Parts\AbstractPart')
                ->isInstanceOf('\BfwSql\Queries\Parts\PartInterface')
            ->string($this->mock->getName())
                ->isEmpty()
            ->variable($this->mock->getShortcut())
                ->isNull()
            ->variable($this->mock->getColumns())
                ->isNull()
            ->boolean($this->mock->getColumnsWithValue())
                ->isFalse()
        ;
    }
    
    public function testGetAndSetColumnsWithValue()
    {
        $this->assert('test Queries\Parts\Table::getColumnsWithValue and setColumnsWithValue')
            ->boolean($this->mock->getColumnsWithValue())
                ->isFalse()
            ->object($this->mock->setColumnsWithValue(false))
                ->isIdenticalTo($this->mock)
            ->boolean($this->mock->getColumnsWithValue())
                ->isFalse()
            ->object($this->mock->setColumnsWithValue(true))
                ->isIdenticalTo($this->mock)
            ->boolean($this->mock->getColumnsWithValue())
                ->isTrue()
        ;
    }
    
    public function testDefineNameAndShortcut()
    {
        $this->assert('test Queries\Parts\Table::defineNameAndShortcut with bad arg')
            ->exception(function() {
                $this->mock->defineNameAndShortcut(42);
            })
                ->hasCode(\BfwSql\Queries\Parts\Table::ERR_TABLE_INFOS_BAD_FORMAT)
        ;
        
        $this->assert('test Queries\Parts\Table::defineNameAndShortcut with array arg')
            ->variable($this->mock->defineNameAndShortcut(['u' => 'user']))
                ->isNull()
            ->string($this->mock->getName())
                ->isEqualTo('test_user')
            ->string($this->mock->getShortcut())
                ->isEqualTo('u')
        ;
        
        $this->assert('test Queries\Parts\Table::defineNameAndShortcut with string arg')
            ->variable($this->mock->defineNameAndShortcut('user'))
                ->isNull()
            ->string($this->mock->getName())
                ->isEqualTo('test_user')
            ->variable($this->mock->getShortcut())
                ->isNull()
        ;
        
        $this->assert('test Queries\Parts\Table::defineNameAndShortcut without table prefix')
            ->given($setTablePrefix = function($prefix) {
                $this->tablePrefix = $prefix;
            })
            ->if($setTablePrefix->call($this->mock, ''))
            ->then
            ->variable($this->mock->defineNameAndShortcut('user'))
                ->isNull()
            ->string($this->mock->getName())
                ->isEqualTo('user')
            ->variable($this->mock->getShortcut())
                ->isNull()
        ;
    }
    
    public function testObtainColumnsClassName()
    {
        $this->assert('test Queries\Parts\Table::obtainColumnsClassName without value')
            ->string($this->mock->obtainColumnsClassName())
                ->isEqualTo('BfwSql\Queries\Parts\ColumnList')
        ;
        
        $this->assert('test Queries\Parts\Table::obtainColumnsClassName with value')
            ->if($this->mock->setColumnsWithValue(true))
            ->then
            ->string($this->mock->obtainColumnsClassName())
                ->isEqualTo('BfwSql\Queries\Parts\ColumnValueList')
        ;
    }
    
    public function testDefineColumns()
    {
        $this->assert('test Queries\Parts\Table::obtainColumnsClassName with string arg')
            ->variable($this->mock->defineColumns('*'))
                ->isNull()
            ->object($columns = $this->mock->getColumns())
                ->isInstanceOf('BfwSql\Queries\Parts\ColumnList')
            ->object($columns->getQuerySystem())
                ->isIdenticalTo($this->mock->getQuerySystem())
            ->object($columns->getTable())
                ->isIdenticalTo($this->mock)
            ->array($columns->getList())
                ->hasSize(1)
            ->string($columns->getList()[0]->getName())
                ->isEqualTo('*')
        ;
        
        $this->assert('test Queries\Parts\Table::obtainColumnsClassName with array arg')
            ->variable($this->mock->defineColumns(['id', 'login']))
                ->isNull()
            ->object($columns = $this->mock->getColumns())
                ->isInstanceOf('BfwSql\Queries\Parts\ColumnList')
            ->object($columns->getQuerySystem())
                ->isIdenticalTo($this->mock->getQuerySystem())
            ->object($columns->getTable())
                ->isIdenticalTo($this->mock)
            ->array($columns->getList())
                ->hasSize(2)
            ->string($columns->getList()[0]->getName())
                ->isEqualTo('id')
            ->string($columns->getList()[1]->getName())
                ->isEqualTo('login')
        ;
        
        $this->assert('test Queries\Parts\Table::obtainColumnsClassName with empty string in arg')
            ->variable($this->mock->defineColumns(null))
                ->isNull()
            ->object($columns = $this->mock->getColumns())
                ->isInstanceOf('BfwSql\Queries\Parts\ColumnList')
            ->object($columns->getQuerySystem())
                ->isIdenticalTo($this->mock->getQuerySystem())
            ->object($columns->getTable())
                ->isIdenticalTo($this->mock)
            ->array($columns->getList())
                ->hasSize(0)
        ;
    }
    
    public function testInvoke()
    {
        $this->assert('test Queries\Parts\Table::__invoke without column in arg')
            ->variable($this->mock->__invoke('user'))
                ->isNull()
            ->mock($this->mock)
                ->call('defineNameAndShortcut')
                    ->withArguments('user')->once()
                ->call('defineColumns')
                    ->withArguments(null)->once()
        ;
        
        $this->assert('test Queries\Parts\Table::__invoke with column in arg')
            ->variable($this->mock->__invoke(['u' => 'user'], ['id', 'login']))
                ->isNull()
            ->mock($this->mock)
                ->call('defineNameAndShortcut')
                    ->withArguments(['u' => 'user'])->once()
                ->call('defineColumns')
                    ->withArguments(['id', 'login'])->once()
        ;
        
        $this->assert('test Queries\Parts\Table::__invoke with empty column in arg')
            ->variable($this->mock->__invoke(['u' => 'user']))
                ->isNull()
            ->mock($this->mock)
                ->call('defineNameAndShortcut')
                    ->withArguments(['u' => 'user'])->once()
                ->call('defineColumns')
                    ->withArguments(null)->once()
        ;
    }
    
    public function testCreateColumnInstance()
    {
        $this->assert('test Queries\Part\Table::createColumnInstance')
            ->variable($this->mock->getColumns())
                ->isNull()
            ->variable($this->mock->createColumnInstance())
                ->isNull()
            ->object($this->mock->getColumns())
                ->isInstanceOf('\BfwSql\Queries\Parts\ColumnList')
            ->mock($this->mock)
                ->call('defineColumns')
                    ->withArguments(null)
                        ->once()
        ;
    }
    
    public function testGenerate()
    {
        $this->assert('test Queries\Parts\Join::generate without shortcut')
            ->if($this->mock->__invoke('user'))
            ->then
            ->string($this->mock->generate())
                ->isEqualTo('`test_user`')
        ;
        
        $this->assert('test Queries\Parts\Join::generate with shortcut')
            ->if($this->mock->__invoke(['u' => 'user']))
            ->then
            ->string($this->mock->generate())
                ->isEqualTo('`test_user` AS `u`')
        ;
    }
}