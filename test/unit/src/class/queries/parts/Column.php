<?php

namespace BfwSql\Queries\Parts\test\unit;

use \Atoum;

$vendorPath = realpath(__DIR__.'/../../../../../../vendor');
require_once($vendorPath.'/autoload.php');

class Column extends Atoum
{
    use \BfwSql\Test\Helpers\CreateModule;
    
    protected $mock;
    protected $query;
    protected $table;
    
    public function beforeTestMethod($testMethod)
    {
        $this->initModule();
        $this->createSqlConnect('myBase');
        
        $this->query = new \mock\BfwSql\Queries\Update($this->sqlConnect); //Need to have getQuoting()
        $this->table = new \mock\BfwSql\Queries\Parts\Table($this->query);
        $this->table->__invoke('table');
        
        if ($testMethod === 'testConstructAndGetter') {
            return;
        }
        
        $this->mock = new \mock\BfwSql\Queries\Parts\Column($this->table, 'type', 't', 'unit');
    }
    
    public function testConstructAndGetter()
    {
        $this->assert('test Queries\Parts\Column::__construct and getter with all args')
            ->object($this->mock = new \mock\BfwSql\Queries\Parts\Column($this->table, 'type', 't', 'unit'))
                ->isInstanceOf('\BfwSql\Queries\Parts\Column')
            ->object($this->mock->getTable())
                ->isIdenticalTo($this->table)
            ->string($this->mock->getName())
                ->isEqualTo('type')
            ->string($this->mock->getShortcut())
                ->isEqualTo('t')
            ->string($this->mock->getValue())
                ->isEqualTo('unit')
        ;
        
        $this->assert('test Queries\Parts\Column::__construct and getter with only mandatory args')
            ->object($this->mock = new \mock\BfwSql\Queries\Parts\Column($this->table, 'type'))
                ->isInstanceOf('\BfwSql\Queries\Parts\Column')
            ->object($this->mock->getTable())
                ->isIdenticalTo($this->table)
            ->string($this->mock->getName())
                ->isEqualTo('type')
            ->variable($this->mock->getShortcut())
                ->isNull()
            ->variable($this->mock->getValue())
                ->isNull()
        ;
    }
    
    public function testObtainName()
    {
        $this->assert('test Queries\Parts\Column::obtainName - prepare')
            ->given($setName = function(string $name) {
                $this->name = $name;
            })
            ->given($setShortcut = function($shortcut) {
                $this->shortcut = $shortcut;
            })
            ->then
        ;
        
        $this->assert('test Queries\Parts\Column::obtainName with column name')
            ->string($this->mock->obtainName())
                ->isEqualTo('`test_table`.`type`')
        ;
        
        $this->assert('test Queries\Parts\Column::obtainName with keyword into name')
            ->if($setName->call($this->mock, 'DISTINCT id'))
            ->then
            ->string($this->mock->obtainName())
                ->isEqualTo('DISTINCT id')
        ;
        
        $this->assert('test Queries\Parts\Column::obtainName with function into name')
            ->if($setName->call($this->mock, 'COUNT(id)'))
            ->then
            ->string($this->mock->obtainName())
                ->isEqualTo('COUNT(id)')
        ;
        
        $this->assert('test Queries\Parts\Column::obtainName with column name and table shortcut')
            ->if($setName->call($this->mock, 'type'))
            ->and($setShortcut->call($this->table, 't'))
            ->then
            ->string($this->mock->obtainName())
                ->isEqualTo('`t`.`type`')
        ;
    }
    
    public function testObtainValue()
    {
        $this->assert('test Queries\Parts\Column::obtainValue - prepare')
            ->given($setValue = function($value) {
                $this->value = $value;
            })
            ->and($this->calling($this->pdo)->quote = function($value) {
                //I know, in reality it's not just that, but it's enough for test
                return '"'.addslashes($value).'"';
            })
        ;
        
        $this->assert('test Queries\Parts\Column::obtainValue with value declared')
            ->string($this->mock->obtainValue())
                ->isEqualTo('"unit"')
        ;
        
        $this->assert('test Queries\Parts\Column::obtainValue without value declared')
            ->if($setValue->call($this->mock, null))
            ->then
            ->string($this->mock->obtainValue())
                ->isEqualTo('null')
        ;
        
        $this->assert('test Queries\Parts\Column::obtainValue with empty value declared')
            ->if($setValue->call($this->mock, ''))
            ->then
            ->string($this->mock->obtainValue())
                ->isEqualTo('""')
        ;
    }
    
    public function testGenerate()
    {
        $this->assert('test Queries\Parts\Column::generate - prepare')
            ->given($setShortcut = function($shortcut) {
                $this->shortcut = $shortcut;
            })
            ->then
        ;
        
        $this->assert('test Queries\Parts\Column::generate with shortcut')
            ->string($this->mock->generate())
                ->isEqualTo('`test_table`.`type` AS `t`')
        ;
        
        $this->assert('test Queries\Parts\Column::generate without shortcut')
            ->if($setShortcut->call($this->mock, null))
            ->then
            ->string($this->mock->generate())
                ->isEqualTo('`test_table`.`type`')
        ;
        
        //And we will not re-test all case of obtainName().
    }
}