<?php

namespace BfwSql\Queries\Parts\test\unit;

use \Atoum;

$vendorPath = realpath(__DIR__.'/../../../../../vendor');
require_once($vendorPath.'/autoload.php');

class ColumnList extends atoum
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
        $this->table = new \mock\BfwSql\Queries\Parts\Table($this->query);
        $this->table->__invoke('table');
        
        if ($testMethod === 'testConstructAndGetter') {
            return;
        }
        
        $this->mock = new \mock\BfwSql\Queries\Parts\ColumnList($this->query, $this->table);
    }
    
    public function testConstructAndGetter()
    {
        $this->assert('test Queries\Parts\ColumnList::__construct and getters')
            ->object($this->mock = new \mock\BfwSql\Queries\Parts\ColumnList($this->query, $this->table))
                ->isInstanceOf('\BfwSql\Queries\Parts\ColumnList')
                ->isInstanceOf('\Iterator')
                ->isInstanceOf('\BfwSql\Queries\Parts\AbstractList')
                ->isInstanceOf('\BfwSql\Queries\Parts\AbstractPart')
                ->isInstanceOf('\BfwSql\Queries\Parts\PartInterface')
            ->object($this->mock->getTable())
                ->isIdenticalTo($this->table)
            ->string($this->mock->getSeparator())
                ->isEqualTo(',')
        ;
    }
    
    public function testInvoke()
    {
        $this->assert('test Queries\Parts\ColumnList::__invoke with one column')
            ->variable($this->mock->__invoke(['n' => 'name']))
                ->isNull()
            ->object($item0 = $this->mock->getList()[0])
                ->string($item0->getName())
                    ->isEqualTo('name')
                ->string($item0->getShortcut())
                    ->isEqualTo('n')
                ->variable($item0->getValue())
                    ->isNull()
                ->object($item0->getTable())
                    ->isIdenticalTo($this->mock->getTable())
        ;
        
        $this->assert('test Queries\Parts\ColumnList::__invoke with many column')
            ->variable($this->mock->__invoke([
                'c' => 'category',
                't' => 'type'
            ]))
                ->isNull()
            ->object($item1 = $this->mock->getList()[1])
                ->isInstanceOf('\BfwSql\Queries\Parts\Column')
                ->string($item1->getName())
                    ->isEqualTo('category')
                ->string($item1->getShortcut())
                    ->isEqualTo('c')
                ->variable($item1->getValue())
                    ->isNull()
                ->object($item1->getTable())
                    ->isIdenticalTo($this->mock->getTable())
            ->object($item2 = $this->mock->getList()[2])
                ->isInstanceOf('\BfwSql\Queries\Parts\Column')
                ->string($item2->getName())
                    ->isEqualTo('type')
                ->string($item2->getShortcut())
                    ->isEqualTo('t')
                ->variable($item2->getValue())
                    ->isNull()
                ->object($item2->getTable())
                    ->isIdenticalTo($this->mock->getTable())
        ;
        
        $this->assert('test Queries\Parts\ColumnList::__invoke without shortcut')
            ->variable($this->mock->__invoke(['dateUpdate']))
                ->isNull()
            ->object($item3 = $this->mock->getList()[3])
                ->isInstanceOf('\BfwSql\Queries\Parts\Column')
                ->string($item3->getName())
                    ->isEqualTo('dateUpdate')
                ->variable($item3->getShortcut())
                    ->isNull()
                ->variable($item3->getValue())
                    ->isNull()
                ->object($item3->getTable())
                    ->isIdenticalTo($this->mock->getTable())
        ;
    }
    
    public function testGenerate()
    {
        $this->assert('test Queries\Parts\ColumnList::generate without column')
            ->string($this->mock->generate())
                ->isEmpty()
        ;
        
        $this->assert('test Queries\Parts\ColumnList::generate with one column')
            ->if($this->mock->__invoke(['n' => 'name']))
            ->then
            ->string($this->mock->generate())
                ->isEqualTo('`test_table`.`name` AS `n`')
        ;
        
        $this->assert('test Queries\Parts\ColumnList::generate with many columns')
            ->if($this->mock->__invoke([
                'c' => 'category',
                't' => 'type'
            ]))
            ->and($this->mock->__invoke(['dateUpdate']))
            ->then
            ->string($this->mock->generate())
                ->isEqualTo(
                    '`test_table`.`name` AS `n`,'
                    .'`test_table`.`category` AS `c`,'
                    .'`test_table`.`type` AS `t`,'
                    .'`test_table`.`dateUpdate`'
                )
        ;
    }
}