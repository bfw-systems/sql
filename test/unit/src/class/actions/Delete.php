<?php

namespace BfwSql\Actions\test\unit;

use \atoum;

$vendorPath = realpath(__DIR__.'/../../../../../vendor');
require_once($vendorPath.'/autoload.php');
require_once($vendorPath.'/bulton-fr/bfw/test/unit/helpers/Application.php');
require_once($vendorPath.'/bulton-fr/bfw/test/unit/helpers/ObserverArray.php');
require_once($vendorPath.'/bulton-fr/bfw/test/unit/mocks/src/class/Module.php');

class Delete extends Atoum
{
    use \BfwSql\Test\Helpers\CreateModule;
    
    protected $mock;
    
    public function beforeTestMethod($testMethod)
    {
        $this->initModule();
        $this->createSqlConnect('myBase');
        
        $this->mockGenerator
            ->makeVisible('assembleRequest')
            ->makeVisible('generateWhere')
            ->makeVisible('addQuotedColumns')
            ->makeVisible('addNotQuotedColumns')
            ->generate('BfwSql\Actions\Delete')
        ;
        
        if ($testMethod === 'testConstruct') {
            return;
        }
        
        $this->mock = new \mock\BfwSql\Actions\Delete($this->sqlConnect, 'myTable');
    }
    
    public function testConstruct()
    {
        $this->assert('test Actions\Delete::__construct')
            ->object($this->mock = new \mock\BfwSql\Actions\Delete($this->sqlConnect, 'myTable'))
                ->isInstanceOf('\BfwSql\Actions\AbstractActions')
                ->isInstanceOf('\BfwSql\Actions\Delete')
            ->string($this->mock->getTableName())
                ->isEqualTo('test_myTable')
        ;
    }
    
    public function testAssembleRequest()
    {
        $this->assert('test Actions\Delete::assembleRequest without where')
            ->if($this->calling($this->mock)->generateWhere = '')
            ->variable($this->mock->assembleRequest())
                ->isNull()
            ->string($this->mock->getAssembledRequest())
                ->isEqualTo('DELETE FROM test_myTable')
        ;
        
        $this->assert('test Actions\Delete::assembleRequest with where')
            ->if($this->calling($this->mock)->generateWhere = ' WHERE lib="atoum"')
            ->variable($this->mock->assembleRequest())
                ->isNull()
            ->string($this->mock->getAssembledRequest())
                ->isEqualTo('DELETE FROM test_myTable WHERE lib="atoum"')
        ;
    }
    
    public function testAddQuotedColumns()
    {
        $this->assert('test Actions\Delete::addQuotedColumns')
            ->exception(function() {
                $this->mock->addQuotedColumns(['lib']);
            })
                ->hasCode(\BfwSql\Actions\AbstractActions::ERR_QUOTED_COLUMN_NOT_SUPPORTED)
        ;
    }
    
    public function testAddNotQuotedColumns()
    {
        $this->assert('test Actions\Delete::addNotQuotedColumns')
            ->exception(function() {
                $this->mock->addNotQuotedColumns(['dateCreate']);
            })
                ->hasCode(\BfwSql\Actions\AbstractActions::ERR_QUOTED_COLUMN_NOT_SUPPORTED)
        ;
    }
}