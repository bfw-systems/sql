<?php

namespace BfwSql\Queries\test\unit;

use \Atoum;

$vendorPath = realpath(__DIR__.'/../../../../vendor');
require_once($vendorPath.'/autoload.php');

class Update extends atoum
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
            ->makeVisible('assembleRequest')
            ->generate('BfwSql\Queries\Update')
        ;
        
        if ($testMethod === 'testConstruct') {
            return;
        }
        
        $this->mock = new \mock\BfwSql\Queries\Update($this->sqlConnect);
    }
    
    public function testConstruct()
    {
        $this->assert('test Queries\Update::__construct with default args')
            ->object($this->mock = new \mock\BfwSql\Queries\Update($this->sqlConnect))
                ->isInstanceOf('\BfwSql\Queries\Update')
                ->isInstanceOf('\BfwSql\Queries\AbstractQuery')
            ->object($this->mock->getQuoting())
                ->isInstanceOf('\BfwSql\Helpers\Quoting')
            ->variable($this->mock->getQuoting()->getQuoteStatus())
                ->isEqualTo(\BfwSql\Helpers\Quoting::QUOTE_ALL)
        ;
        
        $this->assert('test Queries\Update::__construct with columns and quote partially')
            ->object($this->mock = new \mock\BfwSql\Queries\Update(
                $this->sqlConnect,
                \BfwSql\Helpers\Quoting::QUOTE_PARTIALLY
            ))
                ->isInstanceOf('\BfwSql\Queries\Update')
                ->isInstanceOf('\BfwSql\Queries\AbstractQuery')
            ->object($this->mock->getQuoting())
                ->isInstanceOf('\BfwSql\Helpers\Quoting')
            ->variable($this->mock->getQuoting()->getQuoteStatus())
                ->isEqualTo(\BfwSql\Helpers\Quoting::QUOTE_PARTIALLY)
        ;
    }
    
    public function testDefineQueriesParts()
    {
        $this->assert('test Queries\Update::defineQueriesParts')
            ->array($queriesPart = $this->mock->getQueriesParts())
                ->hasKeys([
                    'table',
                    'where',
                    'from',
                    'join',
                    'joinLeft',
                    'joinRight'
                ])
            ->object($queriesPart['table'])
                ->isInstanceOf('\BfwSql\Queries\Parts\Table')
            ->object($queriesPart['table']->getColumns())
                ->isInstanceOf('\BfwSql\Queries\Parts\ColumnValueList')
            ->object($queriesPart['where'])
                ->isInstanceOf('\BfwSql\Queries\Parts\WhereList')
            ->object($queriesPart['from'])
                ->isInstanceOf('\BfwSql\Queries\Parts\Table')
                ->isidenticalTo($queriesPart['table'])
            ->object($queriesPart['join'])
                ->isInstanceOf('\BfwSql\Queries\Parts\JoinList')
            ->object($queriesPart['joinLeft'])
                ->isInstanceOf('\BfwSql\Queries\Parts\JoinList')
            ->object($queriesPart['joinRight'])
                ->isInstanceOf('\BfwSql\Queries\Parts\JoinList')
            ->string($queriesPart['join']->getPartPrefix())
                ->isEqualTo('INNER JOIN')
            ->boolean($queriesPart['join']->getColumnsWithValue())
                ->isTrue()
            ->string($queriesPart['joinLeft']->getPartPrefix())
                ->isEqualTo('LEFT JOIN')
            ->boolean($queriesPart['joinLeft']->getColumnsWithValue())
                ->isTrue()
            ->string($queriesPart['joinRight']->getPartPrefix())
                ->isEqualTo('RIGHT JOIN')
            ->boolean($queriesPart['joinRight']->getColumnsWithValue())
                ->isTrue()
        ;
    }
    
    public function testObtainGenerateOrder()
    {
        $this->assert('test Queries\Update::obtainGenerateOrder')
            ->array($order = $this->mock->obtainGenerateOrder())
            ->given(reset($order))
            ->string(key($order))
                ->isEqualTo('table')
                ->array($table = current($order))
                    ->hasKeys(['prefix', 'callback', 'canBeEmpty'])
                    ->string($table['prefix'])
                        ->isEqualTo('UPDATE')
                    ->array($table['callback'])
                        ->isEqualTo([$this->mock, 'generateValues'])
                    ->boolean($table['canBeEmpty'])
                        ->isFalse()
            ->given(next($order))
            ->string(key($order))
                ->isEqualTo('join')
                ->array(current($order))
                    ->isEmpty()
            ->given(next($order))
            ->string(key($order))
                ->isEqualTo('joinLeft')
                ->array(current($order))
                    ->isEmpty()
            ->given(next($order))
            ->string(key($order))
                ->isEqualTo('joinRight')
                ->array(current($order))
                    ->isEmpty()
            ->given(next($order))
            ->string(key($order))
                ->isEqualTo('where')
                ->array(current($order))
                    ->isEmpty()
            ->boolean(next($order))
                ->isFalse()
        ;
    }
    
    public function testGenerateValues()
    {
        $this->assert('test Queries\Update::generateValues without anything')
            ->exception(function() {
                $this->mock->generateValues();
            })
                ->hasCode(\BfwSql\Queries\Update::ERR_GENERATE_VALUES_NO_DATAS)
        ;
        
        $this->assert('test Queries\Update::generateValues with one column')
            ->if($this->mock->from(
                ['t' => 'table'],
                ['name' => 'atoum']
            ))
            ->then
            ->string($this->mock->generateValues())
                ->isEqualTo('`test_table` AS `t` SET `t`.`name`="atoum"')
        ;
        
        $this->assert('test Queries\Update::generateValues with many columns')
            ->if($this->mock->from(
                ['t' => 'table'],
                [
                    'name' => 'atoum',
                    'type' => 'unit'
                ]
            ))
            ->then
            ->string($this->mock->generateValues())
                ->isEqualTo('`test_table` AS `t` SET `t`.`name`="atoum",`t`.`type`="unit"')
        ;
        
        $this->assert('test Queries\Update::generateValue with join')
            ->if($this->mock->from(
                ['t' => 'table'],
                [
                    'name' => 'atoum',
                    'type' => 'unit'
                ]
            ))
            ->if($this->mock->join(
                ['a' => 'access'],
                'a.idaccess=u.idaccess',
                ['perm' => 'test']
            ))
            ->then
            ->string($this->mock->generateValues())
                ->isEqualTo('`test_table` AS `t` SET `t`.`name`="atoum",`t`.`type`="unit",`a`.`perm`="test"')
        ;
    }
    
    public function testAssembleRequest()
    {
        $this->assert('test Queries\Update::assembleRequest without table')
            ->exception(function() {
                $this->mock->assembleRequest();
            })
                ->hasCode(\BfwSql\Queries\Update::ERR_ASSEMBLE_MISSING_TABLE_NAME)
        ;
        
        $this->assert('test Queries\Update::assembleRequest without columns')
            ->if($this->mock->from('myTable'))
            ->exception(function() {
                $this->mock->assembleRequest();
            })
                ->hasCode(\BfwSql\Queries\Update::ERR_GENERATE_VALUES_NO_DATAS)
        ;
        
        $this->assert('test Queries\Update::assembleRequest with one column')
            ->if($this->mock->from('myTable', ['type' => 'unit']))
            ->then
            ->variable($this->mock->assembleRequest())
                ->isNull()
            ->string($this->mock->getAssembledRequest())
                ->isEqualTo('UPDATE `test_myTable` SET `test_myTable`.`type`="unit"'."\n")
        ;
        
        $this->assert('test Queries\Update::assembleRequest with many columns')
            ->if($this->mock->from(
                'myTable',
                [
                    'type' => 'unit',
                    'lib'  => 'atoum'
                ]
            ))
            ->then
            ->variable($this->mock->assembleRequest())
                ->isNull()
            ->string($this->mock->getAssembledRequest())
                ->isEqualTo(
                    'UPDATE `test_myTable` SET '
                    .'`test_myTable`.`type`="unit",'
                    .'`test_myTable`.`lib`="atoum"'
                    ."\n"
                )
        ;
        
        $this->assert('test Queries\Update::assembleRequest with null columns value')
            ->if($this->mock->from(
                'myTable',
                [
                    'type'    => 'unit',
                    'lib'     => 'atoum',
                    'comment' => null
                ]
            ))
            ->then
            ->variable($this->mock->assembleRequest())
                ->isNull()
            ->string($this->mock->getAssembledRequest())
                ->isEqualTo(
                    'UPDATE `test_myTable` SET '
                    .'`test_myTable`.`type`="unit",'
                    .'`test_myTable`.`lib`="atoum",'
                    .'`test_myTable`.`comment`=null'
                    ."\n"
                )
        ;
        
        $this->assert('test Queries\Update::assembleRequest with null columns value')
            ->if($this->mock->join(
                'test',
                'test.a=myTable.a',
                ['name' => 'test']
            ))
            ->then
            ->variable($this->mock->assembleRequest())
                ->isNull()
            ->string($this->mock->getAssembledRequest())
                ->isEqualTo(
                    'UPDATE `test_myTable` SET '
                    .'`test_myTable`.`type`="unit",'
                    .'`test_myTable`.`lib`="atoum",'
                    .'`test_myTable`.`comment`=null,'
                    .'`test_test`.`name`="test"'."\n"
                    .'INNER JOIN `test_test` ON test.a=myTable.a'
                    ."\n"
                )
        ;
    }
}