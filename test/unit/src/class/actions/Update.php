<?php

namespace BfwSql\Actions\test\unit;

use \atoum;

$vendorPath = realpath(__DIR__.'/../../../../../vendor');
require_once($vendorPath.'/autoload.php');
require_once($vendorPath.'/bulton-fr/bfw/test/unit/helpers/Application.php');
require_once($vendorPath.'/bulton-fr/bfw/test/unit/helpers/ObserverArray.php');
require_once($vendorPath.'/bulton-fr/bfw/test/unit/mocks/src/class/Module.php');

class Update extends Atoum
{
    use \BfwSql\Test\Helpers\CreateModule;
    
    protected $mock;
    
    public function beforeTestMethod($testMethod)
    {
        $this->initModule();
        $this->createSqlConnect('myBase');
        
        $this->mockGenerator
            ->makeVisible('assembleRequest')
            ->makeVisible('quoteValue')
            ->makeVisible('addQuotedColumns')
            ->makeVisible('addNotQuotedColumns')
            ->generate('BfwSql\Actions\Update')
        ;
        
        if ($testMethod === 'testConstruct') {
            return;
        }
        
        $this->mock = new \mock\BfwSql\Actions\Update($this->sqlConnect, 'myTable');
    }
    
    public function testConstruct()
    {
        $this->assert('test Actions\Update::__construct with default args')
            ->object($this->mock = new \mock\BfwSql\Actions\Update($this->sqlConnect, 'myTable'))
                ->isInstanceOf('\BfwSql\Actions\AbstractActions')
                ->isInstanceOf('\BfwSql\Actions\Update')
            ->string($this->mock->getTableName())
                ->isEqualTo('test_myTable')
            ->array($this->mock->getColumns())
                ->isEmpty()
            ->variable($this->mock->getQuoteStatus())
                ->isEqualTo(\BfwSql\Actions\AbstractActions::QUOTE_ALL)
        ;
        
        $this->assert('test Actions\Update::__construct with columns and quote partially')
            ->object($this->mock = new \mock\BfwSql\Actions\Update(
                $this->sqlConnect,
                'myTable',
                ['type' => 'unit', 'lib' => 'atoum'],
                \BfwSql\Actions\AbstractActions::QUOTE_PARTIALLY
            ))
                ->isInstanceOf('\BfwSql\Actions\AbstractActions')
                ->isInstanceOf('\BfwSql\Actions\Update')
            ->string($this->mock->getTableName())
                ->isEqualTo('test_myTable')
            ->array($this->mock->getColumns())
                ->isEqualTo([
                    'type' => 'unit',
                    'lib'  => 'atoum'
                ])
            ->variable($this->mock->getQuoteStatus())
                ->isEqualTo(\BfwSql\Actions\AbstractActions::QUOTE_PARTIALLY)
        ;
    }
    
    public function testAssembleRequest()
    {
        $this->assert('test Actions\Update::assembleRequest - prepare')
            ->if($this->calling($this->mock)->quoteValue = function($key, $str) {
                return '"'.$str.'"';
            })
        ;
        
        $this->assert('test Actions\Update::assembleRequest without columns')
            ->exception(function() {
                $this->mock->assembleRequest();
            })
                ->hasCode(\BfwSql\Actions\Update::ERR_ASSEMBLE_NO_DATAS)
        ;
        
        $this->assert('test Actions\Update::assembleRequest with one column')
            ->if($this->mock->addDatasForColumns(['type' => 'unit']))
            ->then
            ->variable($this->mock->assembleRequest())
                ->isNull()
            ->string($this->mock->getAssembledRequest())
                ->isEqualTo('UPDATE test_myTable SET `type`="unit"')
        ;
        
        $this->assert('test Actions\Update::assembleRequest with many columns')
            ->if($this->mock->addDatasForColumns(['lib' => 'atoum']))
            ->then
            ->variable($this->mock->assembleRequest())
                ->isNull()
            ->string($this->mock->getAssembledRequest())
                ->isEqualTo('UPDATE test_myTable SET `type`="unit",`lib`="atoum"')
        ;
        
        $this->assert('test Actions\Update::assembleRequest with null columns value')
            ->if($this->mock->addDatasForColumns(['comment' => null]))
            ->then
            ->variable($this->mock->assembleRequest())
                ->isNull()
            ->string($this->mock->getAssembledRequest())
                ->isEqualTo('UPDATE test_myTable SET `type`="unit",`lib`="atoum",`comment`=null')
        ;
    }
    
    public function testAddQuotedColumns()
    {
        $this->assert('test Actions\Update::addQuotedColumns')
            //Only check no exception
            ->object($this->mock->addQuotedColumns(['lib']))
                ->isIdenticalTo($this->mock)
        ;
    }
    
    public function testAddNotQuotedColumns()
    {
        $this->assert('test Actions\Update::addNotQuotedColumns')
            //Only check no exception
            ->object($this->mock->addNotQuotedColumns(['dateCreate']))
                ->isIdenticalTo($this->mock)
        ;
    }
}