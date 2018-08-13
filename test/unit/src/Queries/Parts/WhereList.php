<?php

namespace BfwSql\Queries\Parts\test\unit;

use \Atoum;

$vendorPath = realpath(__DIR__.'/../../../../../vendor');
require_once($vendorPath.'/autoload.php');

class WhereList extends atoum
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
        
        $this->mock = new \mock\BfwSql\Queries\Parts\WhereList($this->query);
    }
    
    public function testConstruct()
    {
        $this->assert('test Queries\Parts\WhereList::__construct')
            ->object($this->mock = new \mock\BfwSql\Queries\Parts\WhereList($this->query))
                ->isInstanceOf('\BfwSql\Queries\Parts\WhereList')
                ->isInstanceOf('\BfwSql\Queries\Parts\CommonList')
                ->isInstanceOf('\Iterator')
                ->isInstanceOf('\BfwSql\Queries\Parts\AbstractList')
                ->isInstanceOf('\BfwSql\Queries\Parts\AbstractPart')
                ->isInstanceOf('\BfwSql\Queries\Parts\PartInterface')
            ->string($this->mock->getPartPrefix())
                ->isEqualTo('WHERE')
            ->string($this->mock->getSeparator())
                ->isEqualTo(' AND ')
        ;
    }
    
    public function testInvoke()
    {
        $this->assert('test Queries\Parts\WhereList::__invoke without prepared params')
            ->variable($this->mock->__invoke('type="unit"'))
                ->isNull()
            ->string($this->mock->getList()[0])
                ->isEqualTo('type="unit"')
            ->array($this->query->getPreparedParams())
                ->hasSize(0)
        ;
        
        $this->assert('test Queries\Parts\WhereList::__invoke with another value')
            ->variable($this->mock->__invoke('name=:name', [':name' => 'atoum']))
                ->isNull()
            ->string($this->mock->getList()[0])
                ->isEqualTo('type="unit"')
            ->string($this->mock->getList()[1])
                ->isEqualTo('name=:name')
            ->array($this->query->getPreparedParams())
                ->hasSize(1)
            ->string($this->query->getPreparedParams()[':name'])
                ->isEqualTo('atoum')
        ;
    }
}