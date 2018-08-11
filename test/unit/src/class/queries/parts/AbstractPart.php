<?php

namespace BfwSql\Queries\Parts\test\unit;

use \Atoum;

$vendorPath = realpath(__DIR__.'/../../../../../../vendor');
require_once($vendorPath.'/autoload.php');

class AbstractPart extends Atoum
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
        
        $this->mock = new \mock\BfwSql\Queries\Parts\AbstractPart($this->query);
    }
    
    public function testConstruct()
    {
        $this->assert('test Queries\Parts\AbstractQuery::__construct')
            ->object($this->mock = new \mock\BfwSql\Queries\Parts\AbstractPart($this->query))
                ->isInstanceOf('\BfwSql\Queries\Parts\AbstractPart')
                ->isInstanceOf('\BfwSql\Queries\Parts\PartInterface')
        ;
    }
    
    public function testGetQuerySystem()
    {
        $this->assert('test Queries\Parts\AbstractQuery::getQuerySystem')
            ->object($this->mock->getQuerySystem())
                ->isIdenticalTo($this->query)
        ;
    }
    
    public function testGetTablePrefix()
    {
        $this->assert('test Queries\Parts\AbstractQuery::getTablePrefix')
            ->string($this->mock->getTablePrefix())
                ->isEqualTo('test_')
        ;
    }
    
    public function testGetPartPrefix()
    {
        $this->assert('test Queries\Parts\AbstractQuery::getPartPrefix')
            ->string($this->mock->getPartPrefix())
                ->isEmpty()
        ;
    }
    
    public function testGetUsePartPrefix()
    {
        $this->assert('test Queries\Parts\AbstractQuery::getUsePartPrefix')
            ->boolean($this->mock->getUsePartPrefix())
                ->isTrue()
        ;
    }
    
    public function testGetCanBeEmpty()
    {
        $this->assert('test Queries\Parts\AbstractQuery::getCanBeEmpty')
            ->boolean($this->mock->getCanBeEmpty())
                ->isTrue()
        ;
    }
}