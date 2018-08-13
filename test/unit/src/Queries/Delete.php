<?php

namespace BfwSql\Queries\test\unit;

use \atoum;

$vendorPath = realpath(__DIR__.'/../../../../vendor');
require_once($vendorPath.'/autoload.php');

class Delete extends atoum
{
    use \BfwSql\Test\Helpers\CreateModule;
    
    protected $mock;
    
    public function beforeTestMethod($testMethod)
    {
        $this->initModule();
        $this->createSqlConnect('myBase');
        
        $this->mockGenerator
            ->makeVisible('defineQueriesParts')
            ->makeVisible('obtainGenerateOrder')
            ->makeVisible('assembleRequest')
            ->generate('BfwSql\Queries\Delete')
        ;
        
        if ($testMethod === 'testConstruct') {
            return;
        }
        
        $this->mock = new \mock\BfwSql\Queries\Delete($this->sqlConnect);
    }
    
    public function testConstruct()
    {
        $this->assert('test Queries\Delete::__construct')
            ->object($this->mock = new \mock\BfwSql\Queries\Delete($this->sqlConnect))
                ->isInstanceOf('\BfwSql\Queries\Delete')
                ->isInstanceOf('\BfwSql\Queries\AbstractQuery')
        ;
    }
    
    public function testDefineQueriesParts()
    {
        $this->assert('test Queries\Delete::defineQueriesParts')
            ->array($queriesPart = $this->mock->getQueriesParts())
                ->hasKeys([
                    'table',
                    'where',
                    'from'
                ])
            ->object($queriesPart['table'])
                ->isInstanceOf('\BfwSql\Queries\Parts\Table')
            ->object($queriesPart['where'])
                ->isInstanceOf('\BfwSql\Queries\Parts\WhereList')
            ->object($queriesPart['from'])
                ->isInstanceOf('\BfwSql\Queries\Parts\Table')
                ->isidenticalTo($queriesPart['table'])
        ;
    }
    
    public function testObtainGenerateOrder()
    {
        $this->assert('test Queries\Delete::obtainGenerateOrder')
            ->array($order = $this->mock->obtainGenerateOrder())
            ->given(reset($order))
            ->string(key($order))
                ->isEqualTo('from')
                ->array($from = current($order))
                    ->hasKeys(['prefix', 'canBeEmpty'])
                    ->string($from['prefix'])
                        ->isEqualTo('DELETE FROM')
                    ->boolean($from['canBeEmpty'])
                        ->isFalse()
            ->given(next($order))
            ->string(key($order))
                ->isEqualTo('where')
                ->array(current($order))
                    ->isEmpty()
            ->boolean(next($order))
                ->isFalse()
        ;
    }
    
    public function testAssembleRequest()
    {
        $this->assert('test Queries\Delete::assembleRequest without where')
            ->if($this->mock->from('myTable'))
            ->variable($this->mock->assembleRequest())
                ->isNull()
            ->string($this->mock->getAssembledRequest())
                ->isEqualTo('DELETE FROM `test_myTable`'."\n")
        ;
        
        $this->assert('test Queries\Delete::assembleRequest with where')
            ->if($this->mock->where('lib="atoum"'))
            ->variable($this->mock->assembleRequest())
                ->isNull()
            ->string($this->mock->getAssembledRequest())
                ->isEqualTo(
                    'DELETE FROM `test_myTable`'."\n"
                    .'WHERE lib="atoum"'."\n"
                )
        ;
    }
}