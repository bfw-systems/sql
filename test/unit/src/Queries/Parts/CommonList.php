<?php

namespace BfwSql\Queries\Parts\test\unit;

use \atoum;

$vendorPath = realpath(__DIR__.'/../../../../../vendor');
require_once($vendorPath.'/autoload.php');

class CommonList extends atoum
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
        
        $this->mock = new \mock\BfwSql\Queries\Parts\CommonList($this->query);
    }
    
    public function testConstruct()
    {
        $this->assert('test Queries\Parts\CommonList::__construct')
            ->object($this->mock = new \mock\BfwSql\Queries\Parts\CommonList($this->query))
                ->isInstanceOf('\BfwSql\Queries\Parts\CommonList')
                ->isInstanceOf('\Iterator')
                ->isInstanceOf('\BfwSql\Queries\Parts\AbstractList')
                ->isInstanceOf('\BfwSql\Queries\Parts\AbstractPart')
                ->isInstanceOf('\BfwSql\Queries\Parts\PartInterface')
        ;
    }
    
    public function testSetSeparator()
    {
        $this->assert('test Queries\Parts\CommonList::setSeparator')
            ->string($this->mock->getSeparator())
                ->isEmpty()
            ->object($this->mock->setSeparator(' AND '))
                ->isIdenticalTo($this->mock)
            ->string($this->mock->getSeparator())
                ->isEqualTo(' AND ')
        ;
    }
    
    public function testInvoke()
    {
        $this->assert('test Queries\Parts\CommonList::__invoke')
            ->variable($this->mock->__invoke('type'))
                ->isNull()
            ->string($this->mock->getList()[0])
                ->isEqualTo('type')
        ;
        
        $this->assert('test Queries\Parts\CommonList::__invoke with another value')
            ->variable($this->mock->__invoke('category'))
                ->isNull()
            ->string($this->mock->getList()[0])
                ->isEqualTo('type')
            ->string($this->mock->getList()[1])
                ->isEqualTo('category')
        ;
        
        $this->assert('test Queries\Parts\CommonList::__invoke when it\'s disabled')
            ->if($this->mock->setIsDisabled(true))
            ->then
            ->exception(function() {
                $this->mock->__invoke('category');
            })
                ->hasCode(\BfwSql\Queries\Parts\AbstractPart::ERR_INVOKE_PART_DISABLED)
        ;
    }
}