<?php

namespace BfwSql\Queries\Parts\test\unit;

use \Atoum;

$vendorPath = realpath(__DIR__.'/../../../../../../vendor');
require_once($vendorPath.'/autoload.php');

class ColumnValueList extends atoum
{
    use \BfwSql\Test\Helpers\CreateModule;
    
    protected $mock;
    protected $query;
    protected $table;
    
    public function beforeTestMethod($testMethod)
    {
        $this->initModule();
        $this->createSqlConnect('myBase');
        
        $this->query = new \mock\BfwSql\Queries\Update($this->sqlConnect); //Need to have getQuoting()
        $this->table = new \mock\BfwSql\Queries\Parts\Table($this->query);
        $this->table->__invoke('table');
        
        if ($testMethod === 'testConstruct') {
            return;
        }
        
        $this->mock = new \mock\BfwSql\Queries\Parts\ColumnValueList($this->query, $this->table);
    }
    
    public function testConstruct()
    {
        $this->assert('test Queries\Parts\ColumnValueList::__construct')
            ->object($this->mock = new \mock\BfwSql\Queries\Parts\ColumnValueList($this->query, $this->table))
                ->isInstanceOf('\BfwSql\Queries\Parts\ColumnValueList')
                ->isInstanceOf('\BfwSql\Queries\Parts\ColumnList')
                ->isInstanceOf('\Iterator')
                ->isInstanceOf('\BfwSql\Queries\Parts\AbstractList')
                ->isInstanceOf('\BfwSql\Queries\Parts\AbstractPart')
                ->isInstanceOf('\BfwSql\Queries\Parts\PartInterface')
        ;
    }
    
    public function testInvoke()
    {
        $this->assert('test Queries\Parts\ColumnValueList::__invoke with one column')
            ->variable($this->mock->__invoke(['name' => 'atoum']))
                ->isNull()
            ->object($item0 = $this->mock->getList()[0])
                ->string($item0->getName())
                    ->isEqualTo('name')
                ->variable($item0->getShortcut())
                    ->isNull()
                ->string($item0->getValue())
                    ->isEqualTo('atoum')
                ->object($item0->getTable())
                    ->isIdenticalTo($this->mock->getTable())
        ;
        
        $this->assert('test Queries\Parts\ColumnValueList::__invoke with many column')
            ->variable($this->mock->__invoke([
                'category'   => 'test',
                'type'       => 'unit',
                'dateUpdate' => null
            ]))
                ->isNull()
            ->object($item1 = $this->mock->getList()[1])
                ->isInstanceOf('\BfwSql\Queries\Parts\Column')
                ->string($item1->getName())
                    ->isEqualTo('category')
                ->variable($item1->getShortcut())
                    ->isNull()
                ->string($item1->getValue())
                    ->isEqualTo('test')
                ->object($item1->getTable())
                    ->isIdenticalTo($this->mock->getTable())
            ->object($item2 = $this->mock->getList()[2])
                ->isInstanceOf('\BfwSql\Queries\Parts\Column')
                ->string($item2->getName())
                    ->isEqualTo('type')
                ->variable($item2->getShortcut())
                    ->isNull()
                ->string($item2->getValue())
                    ->isEqualTo('unit')
                ->object($item2->getTable())
                    ->isIdenticalTo($this->mock->getTable())
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
        $this->assert('test Queries\Parts\ColumnValueList::generate - prepare')
            ->if($this->calling($this->pdo)->quote = function($value) {
                //I know, in reality it's not just that, but it's enough for test
                return '"'.addslashes($value).'"';
            })
        ;
        
        $this->assert('test Queries\Parts\ColumnValueList::generate without column')
            ->string($this->mock->generate())
                ->isEmpty()
        ;
        
        $this->assert('test Queries\Parts\ColumnValueList::generate with one column')
            ->if($this->mock->__invoke(['name' => 'atoum']))
            ->then
            ->string($this->mock->generate())
                ->isEqualTo('`test_table`.`name`="atoum"')
        ;
        
        $this->assert('test Queries\Parts\ColumnValueList::generate with many columns')
            ->if($this->mock->__invoke([
                'category'   => 'test',
                'type'       => 'unit',
                'dateUpdate' => null
            ]))
            ->then
            ->string($this->mock->generate())
                ->isEqualTo(
                    '`test_table`.`name`="atoum",'
                    .'`test_table`.`category`="test",'
                    .'`test_table`.`type`="unit",'
                    .'`test_table`.`dateUpdate`=null'
                )
        ;
    }
}