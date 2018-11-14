<?php

namespace BfwSql\Queries\Parts\test\unit;

use \atoum;

$vendorPath = realpath(__DIR__.'/../../../../../vendor');
require_once($vendorPath.'/autoload.php');

class AbstractPart extends atoum
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
            ->makeVisible('invokeCheckIsDisabled')
            ->generate('BfwSql\Queries\Parts\AbstractPart')
        ;
        
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
    
    public function testGetAndSetIsDisabled()
    {
        $this->assert('test Queries\Parts\AbstractQuery::getIsDisabled with default value')
            ->boolean($this->mock->getIsDisabled())
                ->isFalse()
        ;
        
        $this->assert('test Queries\Parts\AbstractQuery::setIsDisabled')
            ->object($this->mock->setIsDisabled(true))
                ->isIdenticalTo($this->mock)
            ->boolean($this->mock->getIsDisabled())
                ->isTrue()
            ->object($this->mock->setIsDisabled(false))
                ->isIdenticalTo($this->mock)
            ->boolean($this->mock->getIsDisabled())
                ->isFalse()
        ;
    }
    
    public function testInvokeCheckIsDisabled()
    {
        $this->assert('test Queries\Parts\AbstractQuery::invokeCheckIsDisabled when it\'s not disabled')
            ->variable($this->mock->invokeCheckIsDisabled())
                ->isNull()
        ;
        
        $this->assert('test Queries\Parts\AbstractQuery::invokeCheckIsDisabled when it\'s disabled')
            ->if($this->mock->setIsDisabled(true))
            ->then
            ->exception(function() {
                $this->mock->invokeCheckIsDisabled();
            })
                ->hasCode(\BfwSql\Queries\Parts\AbstractPart::ERR_INVOKE_PART_DISABLED)
        ;
    }
}