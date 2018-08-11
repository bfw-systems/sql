<?php

namespace BfwSql\Queries\Parts\test\unit;

use \Atoum;

$vendorPath = realpath(__DIR__.'/../../../../../../vendor');
require_once($vendorPath.'/autoload.php');

class JoinList extends Atoum
{
    use \BfwSql\Test\Helpers\CreateModule;
    
    protected $mock;
    protected $query;
    protected $table;
    
    public function beforeTestMethod($testMethod)
    {
        $this->initModule();
        $this->createSqlConnect('myBase');
        
        $this->query = new \mock\BfwSql\Queries\AbstractQuery($this->sqlConnect);
        
        if ($testMethod === 'testConstruct') {
            return;
        }
        
        $this->mock = new \mock\BfwSql\Queries\Parts\JoinList($this->query);
        
        if ($testMethod !== 'testSetPartPrefix') {
            $this->mock->setPartPrefix('LEFT JOIN');
        }
    }
    
    public function testConstruct()
    {
        $this->assert('test Queries\Parts\JoinList::__construct')
            ->object($this->mock = new \mock\BfwSql\Queries\Parts\JoinList($this->query))
                ->isInstanceOf('\BfwSql\Queries\Parts\JoinList')
                ->isInstanceOf('\Iterator')
                ->isInstanceOf('\BfwSql\Queries\Parts\AbstractList')
                ->isInstanceOf('\BfwSql\Queries\Parts\AbstractPart')
                ->isInstanceOf('\BfwSql\Queries\Parts\PartInterface')
            ->string($this->mock->getSeparator())
                ->isEqualTo("\n")
            ->boolean($this->mock->getUsePartPrefix())
                ->isFalse()
        ;
    }
    
    public function testGetAndSetColumnsWithValue()
    {
        $this->assert('test Queries\Parts\JoinList::getColumnsWithValue and setColumnsWithValue')
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
    
    public function testSetPartPrefix()
    {
        $this->assert('test Queries\Parts\JoinList::setPartPrefix')
            ->string($this->mock->getPartPrefix())
                ->isEmpty()
            ->object($this->mock->setPartPrefix('INNER JOIN'))
                ->isIdenticalTo($this->mock)
            ->string($this->mock->getPartPrefix())
                ->isEqualTo('INNER JOIN')
        ;
    }
    
    public function testInvoke()
    {
        $this->assert('test Queries\Parts\JoinList::__invoke without column, shortcut and columns values')
            ->variable($this->mock->__invoke('access', 'test_access.id_access=u.id_access'))
                ->isNull()
            ->object($item0 = $this->mock->getList()[0])
                ->isInstanceOf('\BfwSql\Queries\Parts\Join')
                ->string($item0->getName())
                    ->isEqualTo('test_access')
                ->variable($item0->getShortcut())
                    ->isNull()
                ->string($item0->getOn())
                    ->isEqualTo('test_access.id_access=u.id_access')
                ->boolean($item0->getColumnsWithValue())
                    ->isFalse()
                ->iterator($item0->getColumns())
                    ->isInstanceOf('\BfwSql\Queries\Parts\ColumnList')
                    ->hasSize(1)
                ->string($item0->getColumns()->getList()[0]->getName())
                    ->isEqualTo('*')
        ;
        
        $this->assert('test Queries\Parts\JoinList::__invoke with all')
            ->if($this->mock->setColumnsWithValue(true))
            ->then
            ->variable($this->mock->__invoke(
                ['a' => 'access'],
                'a.id_access=u.id_access',
                ['name' => 'atoum']
            ))
                ->isNull()
            ->object($item1 = $this->mock->getList()[1])
                ->isInstanceOf('\BfwSql\Queries\Parts\Join')
                ->string($item1->getName())
                    ->isEqualTo('test_access')
                ->string($item1->getShortcut())
                    ->isEqualTo('a')
                ->string($item1->getOn())
                    ->isEqualTo('a.id_access=u.id_access')
                ->boolean($item1->getColumnsWithValue())
                    ->isTrue()
                ->iterator($item1->getColumns())
                    ->isInstanceOf('\BfwSql\Queries\Parts\ColumnValueList')
                    ->hasSize(1)
                ->string($item1->getColumns()->getList()[0]->getName())
                    ->isEqualTo('name')
        ;
    }
    
    public function testGenerate()
    {
        $this->assert('test Queries\Parts\JoinList::generate without item')
            ->string($this->mock->generate())
                ->isEmpty()
        ;
        
        $this->assert('test Queries\Parts\JoinList::generate with one item')
            ->if($this->mock->__invoke(
                ['a' => 'access'],
                'a.id_access=u.id_access',
                ['name' => 'atoum']
            ))
            ->then
            ->string($this->mock->generate())
                ->isEqualTo('LEFT JOIN `test_access` AS `a` ON a.id_access=u.id_access')
        ;
        
        $this->assert('test Queries\Parts\JoinList::generate with many items')
            ->if($this->mock->__invoke(['s' => 'sessions'], 's.id_user=u.id_user'))
            ->then
            ->string($this->mock->generate())
                ->isEqualTo(
                    'LEFT JOIN `test_access` AS `a` ON a.id_access=u.id_access'."\n"
                    .'LEFT JOIN `test_sessions` AS `s` ON s.id_user=u.id_user'
                )
        ;
    }
}