<?php

namespace BfwSql\Queries\Parts\test\unit;

use \atoum;

$vendorPath = realpath(__DIR__.'/../../../../../vendor');
require_once($vendorPath.'/autoload.php');

class OrderList extends atoum
{
    use \BfwSql\Test\Helpers\CreateModule;
    
    protected $mock;
    protected $query;
    
    public function beforeTestMethod($testMethod)
    {
        $this->initModule();
        $this->createSqlConnect('myBase');
        
        $this->query = new \mock\BfwSql\Queries\AbstractQuery($this->sqlConnect);
        
        if ($testMethod === 'testConstruct') {
            return;
        }
        
        $this->mock = new \mock\BfwSql\Queries\Parts\OrderList($this->query);
    }
    
    public function testConstruct()
    {
        $this->assert('test Queries\Parts\OrderList::__construct')
            ->object($this->mock = new \mock\BfwSql\Queries\Parts\OrderList($this->query))
                ->isInstanceOf('\BfwSql\Queries\Parts\OrderList')
                ->isInstanceOf('\Iterator')
                ->isInstanceOf('\BfwSql\Queries\Parts\AbstractList')
                ->isInstanceOf('\BfwSql\Queries\Parts\AbstractPart')
                ->isInstanceOf('\BfwSql\Queries\Parts\PartInterface')
            ->string($this->mock->getSeparator())
                ->isEqualTo(',')
            ->string($this->mock->getPartPrefix())
                ->isEqualTo('ORDER BY')
        ;
    }
    
    public function testInvoke()
    {
        $this->assert('test Queries\Parts\OrderList::__invoke and getters without sort arg')
            ->variable($this->mock->__invoke('name'))
                ->isNull()
            ->object($item0 = $this->mock->getList()[0])
                ->isInstanceOf('\BfwSql\Queries\Parts\Order')
                ->string($item0->getExpr())
                    ->isEqualTo('name')
                ->string($item0->getSort())
                    ->isEqualTo('ASC')
        ;
        
        $this->assert('test Queries\Parts\OrderList::__invoke and getters with sort arg')
            ->variable($this->mock->__invoke('login', 'DESC'))
                ->isNull()
            ->object($item1 = $this->mock->getList()[1])
                ->isInstanceOf('\BfwSql\Queries\Parts\Order')
                ->string($item1->getExpr())
                    ->isEqualTo('login')
                ->string($item1->getSort())
                    ->isEqualTo('DESC')
        ;
        
        $this->assert('test Queries\Parts\OrderList::__invoke and getters with sort arg to null')
            ->variable($this->mock->__invoke('RAND()', null))
                ->isNull()
            ->object($item2 = $this->mock->getList()[2])
                ->isInstanceOf('\BfwSql\Queries\Parts\Order')
                ->string($item2->getExpr())
                    ->isEqualTo('RAND()')
                ->variable($item2->getSort())
                    ->isNull()
        ;
        
        $this->assert('test Queries\Parts\OrderList::__invoke when it\'s disabled')
            ->if($this->mock->setIsDisabled(true))
            ->then
            ->exception(function() {
                $this->mock->__invoke('RAND()', null);
            })
                ->hasCode(\BfwSql\Queries\Parts\AbstractPart::ERR_INVOKE_PART_DISABLED)
        ;
    }
    
    public function testGenerate()
    {
        $this->assert('test Queries\Parts\OrderList::generate without item')
            ->string($this->mock->generate())
                ->isEmpty()
        ;
        
        $this->assert('test Queries\Parts\OrderList::generate with one item')
            ->if($this->mock->__invoke('name'))
            ->string($this->mock->generate())
                ->isEqualTo('`name` ASC')
        ;
        
        $this->assert('test Queries\Parts\OrderList::generate with many items')
            ->if($this->mock->__invoke('login', 'DESC'))
            ->string($this->mock->generate())
                ->isEqualTo('`name` ASC,`login` DESC')
            ->then
            ->if($this->mock->__invoke('RAND()', null))
            ->string($this->mock->generate())
                ->isEqualTo('`name` ASC,`login` DESC,RAND()')
        ;
        
        $this->assert('test Queries\Parts\OrderList::generate if disabled')
            ->if($this->mock->setIsDisabled(true))
            ->then
            ->string($this->mock->generate())
                ->isEmpty()
        ;
    }
}