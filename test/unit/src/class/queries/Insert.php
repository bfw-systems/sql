<?php

namespace BfwSql\Queries\test\unit;

use \Atoum;

$vendorPath = realpath(__DIR__.'/../../../../../vendor');
require_once($vendorPath.'/autoload.php');

class Insert extends atoum
{
    use \BfwSql\Test\Helpers\CreateModule;
    
    protected $mock;
    
    public function beforeTestMethod($testMethod)
    {
        $this->initModule();
        $this->createSqlConnect('myBase');
        
        $this->calling($this->pdo)->quote = function($value) {
            //I know, in reality it's not just that, but it's enough for test
            return '"'.addslashes($value).'"';
        };
        
        $this->mockGenerator
            ->makeVisible('defineQueriesParts')
            ->makeVisible('obtainGenerateOrder')
            ->makeVisible('generateValues')
            ->makeVisible('generateSelect')
            ->makeVisible('generateOnDuplicate')
            ->makeVisible('assembleRequest')
            ->generate('BfwSql\Queries\Insert')
        ;
        
        if ($testMethod === 'testConstruct') {
            return;
        }
        
        $this->mock = new \mock\BfwSql\Queries\Insert($this->sqlConnect);
    }
    
    public function testConstruct()
    {
        $this->assert('test Queries\Insert::__construct with default args')
            ->object($this->mock = new \mock\BfwSql\Queries\Insert($this->sqlConnect))
                ->isInstanceOf('\BfwSql\Queries\Insert')
                ->isInstanceOf('\BfwSql\Queries\AbstractQuery')
            ->object($this->mock->getQuoting())
                ->isInstanceOf('\BfwSql\Helpers\Quoting')
            ->variable($this->mock->getQuoting()->getQuoteStatus())
                ->isEqualTo(\BfwSql\Helpers\Quoting::QUOTE_ALL)
        ;
        
        $this->assert('test Queries\Insert::__construct with columns and quote partially')
            ->object($this->mock = new \mock\BfwSql\Queries\Insert(
                $this->sqlConnect,
                \BfwSql\Helpers\Quoting::QUOTE_PARTIALLY
            ))
                ->isInstanceOf('\BfwSql\Queries\Insert')
                ->isInstanceOf('\BfwSql\Queries\AbstractQuery')
            ->object($this->mock->getQuoting())
                ->isInstanceOf('\BfwSql\Helpers\Quoting')
            ->variable($this->mock->getQuoting()->getQuoteStatus())
                ->isEqualTo(\BfwSql\Helpers\Quoting::QUOTE_PARTIALLY)
        ;
    }
    
    public function testDefineQueriesParts()
    {
        $this->assert('test Queries\Insert::defineQueriesParts')
            ->array($queriesPart = $this->mock->getQueriesParts())
                ->hasKeys([
                    'table',
                    'into',
                    'values',
                    'select',
                    'onDuplicate'
                ])
            ->object($queriesPart['table'])
                ->isInstanceOf('\BfwSql\Queries\Parts\Table')
            ->object($queriesPart['into'])
                ->isInstanceOf('\BfwSql\Queries\Parts\Table')
                ->isidenticalTo($queriesPart['table'])
            ->object($queriesPart['values'])
                ->isInstanceOf('\BfwSql\Queries\Parts\ColumnValueList')
            ->object($queriesPart['select'])
                ->isInstanceOf('\BfwSql\Queries\Select')
            ->object($queriesPart['onDuplicate'])
                ->isInstanceOf('\BfwSql\Queries\Parts\ColumnValueList')
        ;
    }
    
    public function testObtainGenerateOrder()
    {
        $this->assert('test Queries\Insert::obtainGenerateOrder')
            ->array($order = $this->mock->obtainGenerateOrder())
            ->given(reset($order))
            ->string(key($order))
                ->isEqualTo('table')
                ->array($table = current($order))
                    ->hasKeys(['prefix', 'canBeEmpty'])
                    ->string($table['prefix'])
                        ->isEqualTo('INSERT INTO')
                    ->boolean($table['canBeEmpty'])
                        ->isFalse()
            ->given(next($order))
            ->string(key($order))
                ->isEqualTo('values')
                ->array($values = current($order))
                    ->hasKeys(['callback', 'usePartPrefix'])
                    ->array($values['callback'])
                        ->isEqualTo([$this->mock, 'generateValues'])
                    ->boolean($values['usePartPrefix'])
                        ->isFalse()
            ->given(next($order))
            ->string(key($order))
                ->isEqualTo('select')
                ->array($select = current($order))
                    ->hasKeys(['callback', 'usePartPrefix'])
                    ->array($select['callback'])
                        ->isEqualTo([$this->mock, 'generateSelect'])
                    ->boolean($values['usePartPrefix'])
                        ->isFalse()
            ->given(next($order))
            ->string(key($order))
                ->isEqualTo('onDuplicate')
                ->array($onDuplicate = current($order))
                    ->hasKeys(['prefix', 'callback'])
                    ->string($onDuplicate['prefix'])
                        ->isEqualTo('ON DUPLICATE KEY UPDATE')
                    ->array($onDuplicate['callback'])
                        ->isEqualTo([$this->mock, 'generateOnDuplicate'])
            ->boolean(next($order))
                ->isFalse()
        ;
    }
    
    public function testSelect()
    {
        $this->assert('test Queries\Insert::select')
            ->object($this->mock->select())
                ->isIdenticalTo($this->mock->getQueriesParts()['select'])
        ;
    }
    
    public function testGenerateValues()
    {
        $this->assert('test Queries\Insert::generateValues without anything')
            ->string($this->mock->generateValues())
                ->isEmpty()
        ;
        
        $this->assert('test Queries\Insert::generateValues with one column')
            ->if($this->mock->values(['name' => 'atoum']))
            ->then
            ->string($this->mock->generateValues())
                ->isEqualTo('(`name`) VALUES ("atoum")')
        ;
        
        $this->assert('test Queries\Insert::generateValues with many columns')
            ->if($this->mock->values(['type' => 'unit']))
            ->then
            ->string($this->mock->generateValues())
                ->isEqualTo('(`name`,`type`) VALUES ("atoum","unit")')
        ;
        
        $this->assert('test Queries\Insert::generateValues with select declared')
            ->if($this->mock->select()->from('table'))
            ->then
            ->string($this->mock->generateValues())
                ->isEqualTo('(`name`,`type`)')
        ;
    }
    
    public function testGenerateSelect()
    {
        $this->assert('test Queries\Insert::generateSelect without request')
            ->string($this->mock->generateSelect())
                ->isEmpty()
        ;
        
        $this->assert('test Queries\Insert::generateSelect with request')
            ->given($select = $this->mock->select())
            ->if($select->from(['u' => 'users'], ['id', 'name', 'mail']))
            ->and($select->where('mail="test@test.com"'))
            ->then
            ->string($this->mock->generateSelect())
                ->isEqualTo(
                    'SELECT `u`.`id`,`u`.`name`,`u`.`mail`'."\n"
                    .'FROM `test_users` AS `u`'."\n"
                    .'WHERE mail="test@test.com"'."\n"
                )
        ;
    }
    
    public function testGenerateOnDuplicate()
    {
        $this->assert('test Queries\Insert::generateOnDuplicate with nothing')
            ->string($this->mock->generateOnDuplicate())
                ->isEmpty()
        ;
        
        $this->assert('test Queries\Insert::generateOnDuplicate with one column')
            ->if($this->mock->onDuplicate(['name' => 'atoum']))
            ->then
            ->string($this->mock->generateOnDuplicate())
                ->isEqualTo('`name`="atoum"')
        ;
        
        $this->assert('test Queries\Insert::generateOnDuplicate with many columns')
            ->if($this->mock->onDuplicate(['type' => 'unit']))
            ->then
            ->string($this->mock->generateOnDuplicate())
                ->isEqualTo('`name`="atoum",`type`="unit"')
        ;
    }
    
    public function testAssembleRequest()
    {
        $this->assert('test Queries\Insert::assembleRequest - prepare')
            ->if($this->mock->into('myTable'))
        ;
        
        $this->assert('test Queries\Insert::assembleRequest without columns')
            ->variable($this->mock->assembleRequest())
                ->isNull()
            ->string($this->mock->getAssembledRequest())
                ->isEqualTo('INSERT INTO `test_myTable`'."\n")
        ;
        
        $this->assert('test Queries\Insert::assembleRequest with one column')
            ->if($this->mock->values(['type' => 'unit']))
            ->then
            ->variable($this->mock->assembleRequest())
                ->isNull()
            ->string($this->mock->getAssembledRequest())
                ->isEqualTo(
                    'INSERT INTO `test_myTable`'."\n"
                    .'(`type`) VALUES ("unit")'."\n"
                )
        ;
        
        $this->assert('test Queries\Insert::assembleRequest with many columns')
            ->if($this->mock->values(['lib' => 'atoum']))
            ->then
            ->variable($this->mock->assembleRequest())
                ->isNull()
            ->string($this->mock->getAssembledRequest())
                ->isEqualTo(
                    'INSERT INTO `test_myTable`'."\n"
                    .'(`type`,`lib`) VALUES ("unit","atoum")'."\n"
                )
        ;
        
        $this->assert('test Queries\Insert::assembleRequest with null columns value')
            ->if($this->mock->values(['comment' => null]))
            ->then
            ->variable($this->mock->assembleRequest())
                ->isNull()
            ->string($this->mock->getAssembledRequest())
                ->isEqualTo(
                    'INSERT INTO `test_myTable`'."\n"
                    .'(`type`,`lib`,`comment`) VALUES ("unit","atoum",null)'."\n"
                )
        ;
        
        $this->assert('test Queries\Insert::assembleRequest with on duplicate')
            ->if($this->mock->onDuplicate([
                'category' => 'test',
                'name'     => 'atoum',
            ]))
            ->then
            ->variable($this->mock->assembleRequest())
                ->isNull()
            ->string($this->mock->getAssembledRequest())
                ->isEqualTo(
                    'INSERT INTO `test_myTable`'."\n"
                    .'(`type`,`lib`,`comment`) VALUES ("unit","atoum",null)'."\n"
                    .'ON DUPLICATE KEY UPDATE `category`="test",`name`="atoum"'."\n"
                )
        ;
        
        $this->assert('test Queries\Insert::assembleRequest with select')
            ->given($this->mock = new \mock\BfwSql\Queries\Insert($this->sqlConnect))
            ->if($this->mock->into('myTable'))
            ->and($this->mock->values([
                'type'    => null,
                'lib'     => null,
                'comment' => null
            ]))
            ->then
            ->given($select = $this->mock->select())
            ->if($select->from(['t' => 'myTable'], ['type', 'lib', 'comment']))
            ->and($select->where('name="test"'))
            ->then
            ->variable($this->mock->assembleRequest())
                ->isNull()
            ->string($this->mock->getAssembledRequest())
                ->isEqualTo(
                    'INSERT INTO `test_myTable`'."\n"
                    .'(`type`,`lib`,`comment`)'."\n"
                    .'SELECT `t`.`type`,`t`.`lib`,`t`.`comment`'."\n"
                    .'FROM `test_myTable` AS `t`'."\n"
                    .'WHERE name="test"'."\n\n"
                )
        ;
    }
}