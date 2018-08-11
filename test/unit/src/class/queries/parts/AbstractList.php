<?php

namespace BfwSql\Queries\Parts\test\unit;

use \Atoum;

$vendorPath = realpath(__DIR__.'/../../../../../../vendor');
require_once($vendorPath.'/autoload.php');

class AbstractList extends Atoum
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
        
        $this->mock = new \mock\BfwSql\Queries\Parts\AbstractList($this->query);
    }
    
    public function testConstruct()
    {
        $this->assert('test Queries\Parts\AbstractList::__construct')
            ->object($this->mock = new \mock\BfwSql\Queries\Parts\AbstractList($this->query))
                ->isInstanceOf('\Iterator')
                ->isInstanceOf('\BfwSql\Queries\Parts\AbstractList')
                ->isInstanceOf('\BfwSql\Queries\Parts\AbstractPart')
                ->isInstanceOf('\BfwSql\Queries\Parts\PartInterface')
        ;
    }
    
    public function testGetList()
    {
        $this->assert('test Queries\Parts\AbstractList::getList for default value')
            ->array($this->mock->getList())
                ->isEmpty()
        ;
        
        $this->assert('test Queries\Parts\AbstractList::getList with an item')
            ->given($addToList = function($data) {
                $this->list[] = $data;
            })
            ->then
            ->if($addToList->call($this->mock, 'atoum'))
            ->then
            ->array($list = $this->mock->getList())
                ->isNotEmpty()
            ->string($list[0])
                ->isEqualTo('atoum')
        ;
    }
    
    public function testGetPosition()
    {
        $this->assert('test Queries\Parts\AbstractList::getPosition')
            ->integer($this->mock->getPosition())
                ->isEqualTo(0)
        ;
    }
    
    public function testGetSeparator()
    {
        $this->assert('test Queries\Parts\AbstractList::getSeparator')
            ->string($this->mock->getSeparator())
                ->isEmpty()
        ;
    }
    
    public function testIterator()
    {
        $this->assert('test Queries\Parts\AbstractList - Iterator')
            ->given($addToList = function($data) {
                $this->list[] = $data;
            })
            ->then
            ->if($addToList->call($this->mock, 'atoum'))
            ->and($addToList->call($this->mock, 'unit'))
            ->and($addToList->call($this->mock, 'test'))
            ->then
            ->iterator($this->mock)
                ->hasSize(3)
            ->integer($this->mock->getPosition())
                ->isEqualTo(3)
            ->then
            ->variable($this->mock->rewind())
                ->isNull()
            ->integer($this->mock->getPosition())
                ->isEqualTo(0)
            ->boolean($this->mock->valid())
                ->isTrue()
            ->integer($this->mock->key())
                ->isEqualTo(0)
            ->string($this->mock->current())
                ->isEqualTo('atoum')
            ->variable($this->mock->next())
                ->isNull()
            ->then
            ->integer($this->mock->getPosition())
                ->isEqualTo(1)
            ->boolean($this->mock->valid())
                ->isTrue()
            ->integer($this->mock->key())
                ->isEqualTo(1)
            ->string($this->mock->current())
                ->isEqualTo('unit')
            ->variable($this->mock->next())
                ->isNull()
            ->then
            ->integer($this->mock->getPosition())
                ->isEqualTo(2)
            ->boolean($this->mock->valid())
                ->isTrue()
            ->integer($this->mock->key())
                ->isEqualTo(2)
            ->string($this->mock->current())
                ->isEqualTo('test')
            ->variable($this->mock->next())
                ->isNull()
            ->then
            ->integer($this->mock->getPosition())
                ->isEqualTo(3)
            ->boolean($this->mock->valid())
                ->isFalse()
        ;
    }
    
    public function testGenerate()
    {
        $this->assert('test Queries\Parts\AbstractList::generate - prepare')
            ->given($addToList = function($data) {
                $this->list[] = $data;
            })
            ->then
        ;
        
        $this->assert('test Queries\Parts\AbstractList::generate without datas')
            ->string($this->mock->generate())
                ->isEmpty()
        ;
        
        $this->assert('test Queries\Parts\AbstractList::generate with one datas')
            ->if($addToList->call($this->mock, 'atoum'))
            ->then
            ->string($this->mock->generate())
                ->isEqualTo('atoum')
        ;
        
        $this->assert('test Queries\Parts\AbstractList::generate with many datas')
            ->if($addToList->call($this->mock, 'unit'))
            ->and($addToList->call($this->mock, 'test'))
            ->then
            ->string($this->mock->generate())
                ->isEqualTo('atoumunittest')
        ;
        
        $this->assert('test Queries\Parts\AbstractList::generate with many datas and a separator')
            ->given($setSeparator = function($separator) {
                $this->separator = $separator;
            })
            ->if($setSeparator->call($this->mock, ', '))
            ->then
            ->string($this->mock->generate())
                ->isEqualTo('atoum, unit, test')
        ;
    }
}