<?php

namespace BfwSql\Queries\Parts\test\unit;

use \Atoum;

$vendorPath = realpath(__DIR__.'/../../../../../vendor');
require_once($vendorPath.'/autoload.php');

class Order extends atoum
{
    use \BfwSql\Test\Helpers\CreateModule;
    
    protected $mock;
    protected $query;
    protected $table;
    
    public function beforeTestMethod($testMethod)
    {
        $this->initModule();
        $this->createSqlConnect('myBase');
    }
    
    public function testConstructAndGetter()
    {
        $this->assert('test Queries\Parts\Order::__construct and getters without sort arg')
            ->object($this->mock = new \mock\BfwSql\Queries\Parts\Order('name'))
                ->isInstanceOf('\BfwSql\Queries\Parts\Order')
            ->string($this->mock->getExpr())
                ->isEqualTo('name')
            ->string($this->mock->getSort())
                ->isEqualTo('ASC')
        ;
        
        $this->assert('test Queries\Parts\Order::__construct and getters with sort arg')
            ->object($this->mock = new \mock\BfwSql\Queries\Parts\Order('login', 'DESC'))
                ->isInstanceOf('\BfwSql\Queries\Parts\Order')
            ->string($this->mock->getExpr())
                ->isEqualTo('login')
            ->string($this->mock->getSort())
                ->isEqualTo('DESC')
        ;
        
        $this->assert('test Queries\Parts\Order::__construct and getters with sort arg to null')
            ->object($this->mock = new \mock\BfwSql\Queries\Parts\Order('login', null))
                ->isInstanceOf('\BfwSql\Queries\Parts\Order')
            ->string($this->mock->getExpr())
                ->isEqualTo('login')
            ->variable($this->mock->getSort())
                ->isNull()
        ;
    }
    
    public function testGenerate()
    {
        $this->assert('test Queries\Parts\Order::generate with column name and sort')
            ->if($this->mock = new \mock\BfwSql\Queries\Parts\Order('login', 'DESC'))
            ->then
            ->string($this->mock->generate())
                ->isEqualTo('`login` DESC')
        ;
        
        $this->assert('test Queries\Parts\Order::generate with function and without sort')
            ->if($this->mock = new \mock\BfwSql\Queries\Parts\Order('RAND()', null))
            ->then
            ->string($this->mock->generate())
                ->isEqualTo('RAND()')
        ;
    }
}