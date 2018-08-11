<?php

namespace BfwSql\Queries\Parts\test\unit;

use \Atoum;

$vendorPath = realpath(__DIR__.'/../../../../../../vendor');
require_once($vendorPath.'/autoload.php');

class Limit extends Atoum
{
    use \BfwSql\Test\Helpers\CreateModule;
    
    protected $mock;
    protected $query;
    
    public function beforeTestMethod($testMethod)
    {
        $this->initModule();
        $this->createSqlConnect('myBase');
        
        $this->query = new \mock\BfwSql\Queries\AbstractQuery($this->sqlConnect);
        
        if ($testMethod === 'testConstructAndGetters') {
            return;
        }
        
        $this->mock = new \mock\BfwSql\Queries\Parts\Limit($this->query);
    }
    
    public function testConstructAndGetters()
    {
        $this->assert('test Queries\Parts\Limit::__construct')
            ->object($this->mock = new \mock\BfwSql\Queries\Parts\Limit($this->query))
                ->isInstanceOf('\BfwSql\Queries\Parts\Limit')
                ->isInstanceOf('\BfwSql\Queries\Parts\AbstractPart')
                ->isInstanceOf('\BfwSql\Queries\Parts\PartInterface')
            ->string($this->mock->getPartPrefix())
                ->isEqualTo('LIMIT')
            ->variable($this->mock->getRowCount())
                ->isNull()
            ->variable($this->mock->getOffset())
                ->isNull()
        ;
    }
    
    public function testInvoke()
    {
        $this->assert('test Queries\Parts\Limit::__invoke with one arg')
            ->variable($this->mock->__invoke(10))
                ->isNull()
            ->integer($this->mock->getRowCount())
                ->isEqualTo(10)
            ->variable($this->mock->getOffset())
                ->isNull()
        ;
        
        $this->assert('test Queries\Parts\Limit::__invoke with two arg')
            ->variable($this->mock->__invoke(5, 10))
                ->isNull()
            ->integer($this->mock->getRowCount())
                ->isEqualTo(10)
            ->integer($this->mock->getOffset())
                ->isEqualTo(5)
        ;
    }
    
    public function testGenerate()
    {
        $this->assert('test Queries\Parts\Limit::generate without anything declared')
            ->string($this->mock->generate())
                ->isEqualTo('')
        ;
        
        $this->assert('test Queries\Parts\Limit::generate with only row count')
            ->if($this->mock->__invoke(10))
            ->then
            ->string($this->mock->generate())
                ->isEqualTo('10')
        ;
        
        $this->assert('test Queries\Parts\Limit::generate with row count and offset')
            ->if($this->mock->__invoke(5, 10))
            ->then
            ->string($this->mock->generate())
                ->isEqualTo('5, 10')
        ;
    }
}