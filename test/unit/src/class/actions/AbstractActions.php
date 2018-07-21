<?php

namespace BfwSql\Actions\test\unit;

use \atoum;

$vendorPath = realpath(__DIR__.'/../../../../../vendor');
require_once($vendorPath.'/autoload.php');
require_once($vendorPath.'/bulton-fr/bfw/test/unit/helpers/Application.php');
require_once($vendorPath.'/bulton-fr/bfw/test/unit/helpers/ObserverArray.php');
require_once($vendorPath.'/bulton-fr/bfw/test/unit/mocks/src/class/Module.php');

class AbstractActions extends Atoum
{
    use \BfwSql\Test\Helpers\CreateModule;
    
    protected $mock;
    
    public function beforeTestMethod($testMethod)
    {
        $this->initModule();
        $this->createSqlConnect('myBase');
        
        $this->mockGenerator
            ->makeVisible('assembleRequest')
            ->makeVisible('executeQuery')
            ->makeVisible('addPreparedRequestArgs')
            ->makeVisible('generateWhere')
            ->makeVisible('quoteValue')
            ->makeVisible('callObserver')
            ->generate('BfwSql\Actions\Test\Mocks\AbstractActions')
        ;
        
        if ($testMethod === 'testConstructAndGetSqlConnect') {
            return;
        }
        
        $this->mock = new \mock\BfwSql\Actions\Test\Mocks\AbstractActions($this->sqlConnect);
    }
    
    protected function disablePdoRequest()
    {
        $prepareReturn = new \mock\PDOStatement;
        $queryReturn   = new \mock\PDOStatement;
        $execReturn    = new \mock\PDOStatement;
        
        $this
            ->if($this->calling($this->pdo)->prepare = $prepareReturn)
            ->and($this->calling($this->pdo)->query = $queryReturn)
            ->and($this->calling($this->pdo)->exec = $execReturn)
        ;
        
        return (object) [
            'prepareReturn' => $prepareReturn,
            'queryReturn'   => $queryReturn,
            'execReturn'    => $execReturn
        ];
    }
    
    public function testConstructAndGetSqlConnect()
    {
        $this->assert('test Actions\AbstractActions::__construct')
            ->object($this->mock = new \mock\BfwSql\Actions\AbstractActions($this->sqlConnect))
                ->isInstanceOf('\BfwSql\Actions\AbstractActions')
        ;
        
        $this->assert('test Actions\AbstractActions::getSqlConnect')
            ->object($this->mock->getSqlConnect())
                ->isIdenticalTo($this->sqlConnect)
        ;
    }
    
    public function testGetAssembledRequest()
    {
        $this->assert('test Actions\AbstractActions::getAssembledRequest for default value')
            ->string($this->mock->getAssembledRequest())
                ->isEmpty()
        ;
    }
    
    public function testGetAndSetIsPreparedRequest()
    {
        $this->assert('test Actions\AbstractActions::getIsPreparedRequest for default value')
            ->boolean($this->mock->getIsPreparedRequest())
                ->isTrue()
        ;
        
        $this->assert('test Actions\AbstractActions::setIsPreparedRequest with bool value')
            ->object($this->mock->setIsPreparedRequest(false))
                ->isIdenticalTo($this->mock)
            ->boolean($this->mock->getIsPreparedRequest())
                ->isFalse()
        ;
        
        $this->assert('test Actions\AbstractActions::setIsPreparedRequest with non bool value')
            ->object($this->mock->setIsPreparedRequest(1))
                ->isIdenticalTo($this->mock)
            ->boolean($this->mock->getIsPreparedRequest())
                ->isTrue()
        ;
    }
    
    public function testGetTableName()
    {
        $this->assert('test Actions\AbstractActions::getTableName for default value')
            ->string($this->mock->getTableName())
                ->isEmpty()
        ;
    }
    
    public function testGetColumns()
    {
        $this->assert('test Actions\AbstractActions::getColumns for default value')
            ->array($this->mock->getColumns())
                ->isEmpty()
        ;
    }
    
    public function testGetQuoteStatus()
    {
        $this->assert('test Actions\AbstractActions::getQuoteStatus for default value')
            ->variable($this->mock->getQuoteStatus())
                ->isEqualTo(\BfwSql\Actions\AbstractActions::QUOTE_ALL)
        ;
    }
    
    public function testGetPartiallyPreferedMode()
    {
        $this->assert('test Actions\AbstractActions::getPartiallyPreferedMode for default value')
            ->variable($this->mock->getPartiallyPreferedMode())
                ->isEqualTo(\BfwSql\Actions\AbstractActions::PARTIALLY_MODE_QUOTE)
        ;
    }
    
    public function testGetQuotedColumns()
    {
        $this->assert('test Actions\AbstractActions::getQuotedColumns for default value')
            ->array($this->mock->getQuotedColumns())
                ->isEmpty()
        ;
    }
    
    public function testGetNotQuotedColumns()
    {
        $this->assert('test Actions\AbstractActions::getNotQuotedColumns for default value')
            ->array($this->mock->getNotQuotedColumns())
                ->isEmpty()
        ;
    }
    
    public function testGetWhere()
    {
        $this->assert('test Actions\AbstractActions::getWhere for default value')
            ->array($this->mock->getWhere())
                ->isEmpty()
        ;
    }
    
    public function testGetPreparedRequestArgs()
    {
        $this->assert('test Actions\AbstractActions::getPreparedRequestArgs for default value')
            ->array($this->mock->getPreparedRequestArgs())
                ->isEmpty()
        ;
    }
    
    public function testGetAndSetPrepareDriversOptions()
    {
        $this->assert('test Actions\AbstractActions::getPrepareDriversOptions for default value')
            ->array($this->mock->getPrepareDriversOptions())
                ->isEmpty()
        ;
        
        $this->assert('test Actions\AbstractActions::setPrepareDriversOptions')
            ->object($this->mock->setPrepareDriversOptions([
                \PDO::ATTR_CURSOR => \PDO::CURSOR_FWDONLY
            ]))
                ->isIdenticalTo($this->mock)
            ->array($this->mock->getPrepareDriversOptions())
                ->isEqualTo([
                    \PDO::ATTR_CURSOR => \PDO::CURSOR_FWDONLY
                ])
        ;
    }
    
    public function testGetNoResult()
    {
        $this->assert('test Actions\AbstractActions::getNoResult for default value')
            ->boolean($this->mock->getNoResult())
                ->isFalse()
        ;
    }
    
    public function testGetLastRequestStatement()
    {
        $this->assert('test Actions\AbstractActions::getLastRequestStatement for default value')
            ->variable($this->mock->getLastRequestStatement())
                ->isNull()
        ;
    }
    
    public function testGetLastErrorInfos()
    {
        $this->assert('test Actions\AbstractActions::getLastErrorInfos for default value')
            ->array($this->mock->getLastErrorInfos())
                ->isEmpty()
        ;
    }
    
    public function testIsAssembled()
    {
        $this->assert('test Actions\AbstractActions::isAssembled for default value')
            ->boolean($this->mock->isAssembled())
                ->isFalse()
        ;
        
        $this->assert('test Actions\AbstractActions::isAssembled with an assembled request')
            ->if($this->mock->setAssembledRequest('SELECT id FROM test'))
            ->then
            ->boolean($this->mock->isAssembled())
                ->isTrue()
        ;
    }
    
    public function testAssemble()
    {
        $this->assert('test Actions\AbstractActions::assemble - prepare (to assemble)')
            ->if($this->calling($this->mock)->isAssembled = false)
            ->and($this->calling($this->mock)->assembleRequest = null)
        ;
        
        $this->assert('test Actions\AbstractActions::assemble when request is not assembled and not forced')
            ->string($this->mock->assemble())
                ->isEmpty()
            ->mock($this->mock)
                ->call('isAssembled')
                    ->once()
                ->call('assembleRequest')
                    ->once()
        ;
        
        $this->assert('test Actions\AbstractActions::assemble when request is not assembled and forced')
            ->string($this->mock->assemble(true))
                ->isEmpty()
            ->mock($this->mock)
                ->call('isAssembled')
                    ->once()
                ->call('assembleRequest')
                    ->once()
        ;
        
        $this->assert('test Actions\AbstractActions::assemble - prepare (already assembled)')
            ->if($this->mock->setAssembledRequest('SELECT id FROM test'))
            ->and($this->calling($this->mock)->isAssembled = true)
            ->and($this->calling($this->mock)->assembleRequest = null)
        ;
        
        $this->assert('test Actions\AbstractActions::assemble when request is assembled and not forced')
            ->string($this->mock->assemble())
                ->isEqualTo('SELECT id FROM test')
            ->mock($this->mock)
                ->call('isAssembled')
                    ->once()
                ->call('assembleRequest')
                    ->never()
        ;
        
        $this->assert('test Actions\AbstractActions::assemble when request is assembled and forced')
            ->string($this->mock->assemble(true))
                ->isEqualTo('SELECT id FROM test')
            ->mock($this->mock)
                ->call('isAssembled')
                    ->once()
                ->call('assembleRequest')
                    ->once()
        ;
    }
    
    public function testExecuteQueryWithPreparedRequest()
    {
        $this->assert('test Actions\AbstractActions::executeQuery with prepared request - prepare')
            ->given($statement = new \mock\PDOStatement)
            ->if($this->calling($this->pdo)->prepare = $statement)
            ->if($this->calling($statement)->execute = null)
            ->and($this->calling($this->pdo)->errorInfo = function() {
                //From http://php.net/manual/en/pdostatement.errorinfo.php
                return [
                    0 => '00000',
                    1 => null,
                    2 => null
                ];
            })
            ->then
            
            ->if($this->mock->setAssembledRequest('SELECT id FROM test'))
            ->and($this->calling($this->mock)->assemble = null)
            ->and($this->calling($this->mock)->callObserver = null)
        ;
        
        $this->assert('test Actions\AbstractActions::executeQuery with prepared request')
            ->array($this->mock->executeQuery())
                ->isEqualTo([
                    0 => '00000',
                    1 => null,
                    2 => null
                ])
            ->integer($this->sqlConnect->getNbQuery())
                ->isEqualTo(1)
            ->object($this->mock->getLastRequestStatement())
                ->isIdenticalTo($statement)
            ->array($this->mock->getLastErrorInfos())
                ->isEqualTo([
                    0 => '00000',
                    1 => null,
                    2 => null
                ])
            ->mock($this->mock)
                ->call('assemble')
                    ->once()
                ->call('callObserver')
                    ->once()
            ->mock($this->pdo)
                ->call('prepare')
                    ->once()
        ;
    }
    
    public function testExecuteQueryWithoutPreparedRequest()
    {
        $this->assert('test Actions\AbstractActions::executeQuery without prepared request - prepare')
            ->if($this->mock->setIsPreparedRequest(false))
            ->then
            
            ->given($statement = new \mock\PDOStatement)
            ->if($this->calling($this->pdo)->exec = $statement)
            ->if($this->calling($this->pdo)->query = $statement)
            ->if($this->calling($statement)->execute = null)
            ->and($this->calling($this->pdo)->errorInfo = function() {
                //From http://php.net/manual/en/pdostatement.errorinfo.php
                return [
                    0 => '00000',
                    1 => null,
                    2 => null
                ];
            })
            ->then
            
            ->if($this->mock->setAssembledRequest('SELECT id FROM test'))
            ->and($this->calling($this->mock)->assemble = null)
            ->and($this->calling($this->mock)->callObserver = null)
        ;
        
        $this->assert('test Actions\AbstractActions::executeQuery without prepared request')
            ->array($this->mock->executeQuery())
                ->isEqualTo([
                    0 => '00000',
                    1 => null,
                    2 => null
                ])
            ->integer($this->sqlConnect->getNbQuery())
                ->isEqualTo(1)
            ->object($this->mock->getLastRequestStatement())
                ->isIdenticalTo($statement)
            ->array($this->mock->getLastErrorInfos())
                ->isEqualTo([
                    0 => '00000',
                    1 => null,
                    2 => null
                ])
            ->mock($this->mock)
                ->call('assemble')
                    ->once()
                ->call('callObserver')
                    ->once()
            ->mock($this->pdo)
                ->call('exec') //query only if call is \BfwSql\Actions\Select (or extended it)
                    ->once()
        ;
    }
    
    public function testExecute()
    {
        $this->assert('test Actions\AbstractActions::execute when the execution have an error')
            ->if($this->calling($this->mock)->executeQuery = function() {
                //From http://php.net/manual/en/pdostatement.errorinfo.php
                return [
                    0 => '42S02',
                    1 => -204,
                    2 => '[IBM][CLI Driver][DB2/LINUX] SQL0204N  "DANIELS.BONES" is an undefined name.  SQLSTATE=42704'
                ];
            })
            ->then
            
            ->exception(function() {
                $this->mock->execute();
            })
                ->hasCode(\BfwSql\Actions\AbstractActions::ERR_EXECUTE_BAD_REQUEST)
        ;
        
        $this->assert('test Actions\AbstractActions::execute when the execution have an not detected error')
            ->if($this->calling($this->mock)->executeQuery = function() {
                return [
                    0 => '00000',
                    1 => null,
                    2 => null
                ];
            })
            ->and($this->mock->setLastRequestStatement(false))
            ->then
            
            ->exception(function() {
                $this->mock->execute();
            })
                ->hasCode(\BfwSql\Actions\AbstractActions::ERR_EXECUTED_UNKNOWN_ERROR)
        ;
        
        $this->assert('test Actions\AbstractActions::execute without error but no row impacted')
            ->given($statement = new \mock\PDOStatement)
            ->if($this->calling($this->mock)->executeQuery = function() {
                return [
                    0 => '00000',
                    1 => null,
                    2 => null
                ];
            })
            ->and($this->mock->setLastRequestStatement($statement))
            ->and($this->calling($this->mock)->obtainImpactedRows = 0)
            ->then
            
            ->object($this->mock->execute())
                ->isIdenticalTo($statement)
            ->boolean($this->mock->getNoResult())
                ->isTrue()
        ;
        
        $this->assert('test Actions\AbstractActions::execute without error and with many rows impacted')
            ->given($statement = new \mock\PDOStatement)
            ->if($this->calling($this->mock)->executeQuery = function() {
                return [
                    0 => '00000',
                    1 => null,
                    2 => null
                ];
            })
            ->and($this->mock->setLastRequestStatement($statement))
            ->and($this->calling($this->mock)->obtainImpactedRows = 10)
            ->then
            
            ->object($this->mock->execute())
                ->isIdenticalTo($statement)
            ->boolean($this->mock->getNoResult())
                ->isFalse()
        ;
    }
    
    public function testCloseCursor()
    {
        $this->assert('test Actions\AbstractActions::closeCursor')
            ->given($statement = new \mock\PDOStatement)
            ->if($this->calling($statement)->closeCursor = null)
            ->and($this->mock->setLastRequestStatement($statement))
            ->then
            
            ->variable($this->mock->closeCursor())
                ->isNull() //Because mock
            ->mock($statement)
                ->call('closeCursor')
                    ->once()
        ;
    }
    
    public function testObtainImpactedRows()
    {
        $this->assert('test Actions\AbstractActions::obtainImpactedRows if no request have been executed')
            ->boolean($this->mock->obtainImpactedRows())
                ->isFalse()
        ;
        
        $this->assert('test Actions\AbstractActions::obtainImpactedRows if last statement is an object')
            ->given($statement = new \mock\PDOStatement)
            ->if($this->calling($statement)->rowCount = 42)
            ->and($this->mock->setLastRequestStatement($statement))
            ->then
            ->integer($this->mock->obtainImpactedRows())
                ->isEqualTo(42)
            ->mock($statement)
                ->call('rowCount')
                    ->once()
        ;
        
        $this->assert('test Actions\AbstractActions::obtainImpactedRows if last statement is an integer')
            ->if($this->mock->setLastRequestStatement(10))
            ->then
            ->integer($this->mock->obtainImpactedRows())
                ->isEqualTo(10)
        ;
    }
    
    public function testQuery()
    {
        $this->assert('test Actions\AbstractActions::query')
            ->string($this->mock->getAssembledRequest())
                ->isEmpty()
            ->object($this->mock->query('SELECT id FROM test'))
                ->isIdenticalTo($this->mock)
            ->string($this->mock->getAssembledRequest())
                ->isEqualTo('SELECT id FROM test')
        ;
    }
    
    public function testWhere()
    {
        $this->assert('test Actions\AbstractActions::where - prepare')
            ->if($this->calling($this->mock)->addPreparedRequestArgs = null)
        ;
        
        $this->assert('test Actions\AbstractActions::where without prepared filter')
            ->array($this->mock->getWhere())
                ->isEmpty()
            ->object($this->mock->where('type="unit"'))
                ->isIdenticalTo($this->mock)
            ->array($this->mock->getWhere())
                ->isEqualTo(['type="unit"'])
            ->mock($this->mock)
                ->call('addPreparedRequestArgs')
                    ->never()
        ;
        
        $this->assert('test Actions\AbstractActions::where without prepared filter')
            ->array($this->mock->getWhere())
                ->isEqualTo(['type="unit"']) //Previous test
            ->object($this->mock->where('lib=:lib', [':lib' => 'atoum']))
                ->isIdenticalTo($this->mock)
            ->array($this->mock->getWhere())
                ->isEqualTo(['type="unit"', 'lib=:lib'])
            ->mock($this->mock)
                ->call('addPreparedRequestArgs')
                    ->withArguments([':lib' => 'atoum'])
                        ->once()
        ;
    }
    
    public function testAddPreparedRequestArgs()
    {
        $this->assert('test Actions\AbstractActions::addPreparedRequestArgs')
            ->array($this->mock->getPreparedRequestArgs())
                ->isEmpty()
            ->object($this->mock->addPreparedRequestArgs([':type' => 'unit']))
                ->isIdenticalTo($this->mock)
            ->array($this->mock->getPreparedRequestArgs())
                ->isEqualTo([':type' => 'unit'])
            ->object($this->mock->addPreparedRequestArgs([':name' => 'atoum']))
                ->isIdenticalTo($this->mock)
            ->array($this->mock->getPreparedRequestArgs())
                ->isEqualTo([
                    ':type' => 'unit',
                    ':name' => 'atoum'
                ])
        ;
    }
    
    public function testGenerateWhere()
    {
        $this->assert('test Actions\AbstractActions::generateWhere without filters')
            ->string($this->mock->generateWhere())
                ->isEmpty()
        ;
        
        $this->assert('test Actions\AbstractActions::generateWhere with one filter')
            ->if($this->mock->setWhere(['type="unit"']))
            ->then
            ->string($this->mock->generateWhere())
                ->isEqualTo(' WHERE type="unit"')
        ;
        
        $this->assert('test Actions\AbstractActions::generateWhere with may filter')
            ->if($this->mock->setWhere(['type="unit"', 'lib="atoum"']))
            ->then
            ->string($this->mock->generateWhere())
                ->isEqualTo(' WHERE type="unit" AND lib="atoum"')
        ;
    }
    
    public function testAddDatasForColumns()
    { 
        $this->assert('test Actions\AbstractActions::addDatasForColumns with column already declared with same value and new column')
            ->array($this->mock->getColumns())
                ->isEmpty()
            ->object($this->mock->addDatasForColumns([
                'type' => 'unit',
                'name' => 'atoum'
            ]))
                ->isIdenticalTo($this->mock)
            ->array($this->mock->getColumns())
                ->isEqualTo([
                    'type' => 'unit',
                    'name' => 'atoum'
                ])
        ;
        
        $this->assert('test Actions\AbstractActions::addDatasForColumns with a column declared but with different value')
            ->array($this->mock->getColumns())
                ->isEqualTo([
                    'type' => 'unit',
                    'name' => 'atoum'
                ])
            ->exception(function() {
                $this->mock->addDatasForColumns([
                    'type' => 'unit-test',
                    'name' => 'atoum'
                ]);
            })
                ->hasCode(\BfwSql\Actions\AbstractActions::ERR_DATA_ALREADY_DECLARED_FOR_COLUMN)
            ->array($this->mock->getColumns())
                ->isEqualTo([
                    'type' => 'unit',
                    'name' => 'atoum'
                ])
        ;
    }
    
    public function testAddQuotedColumns()
    {
        $this->assert('test Actions\AbstractActions::addQuotedColumns for new columns')
            ->array($this->mock->getQuotedColumns())
                ->isEmpty()
            ->object($this->mock->addQuotedColumns(['lib', 'type']))
                ->isIdenticalTo($this->mock)
            ->array($this->mock->getQuotedColumns())
                ->isEqualTo([
                    'lib' => true,
                    'type' => true
                ])
        ;
        
        $this->assert('test Actions\AbstractActions::addQuotedColumns with a non-quoted column')
            ->if($this->mock->setNotQuotedColumns(['used' => true]))
            ->then
            ->exception(function() {
                $this->mock->addQuotedColumns(['dateCreate', 'used', 'dateEdit']);
            })
                ->hasCode(\BfwSql\Actions\AbstractActions::ERR_COLUMN_ALREADY_DEFINE_NOT_QUOTED)
            ->array($this->mock->getQuotedColumns())
                ->isEqualTo([
                    'lib'        => true,
                    'type'       => true,
                    'dateCreate' => true
                ])
        ;
    }
    
    public function testAddNotQuotedColumns()
    {
        $this->assert('test Actions\AbstractActions::addNotQuotedColumns for new columns')
            ->array($this->mock->getNotQuotedColumns())
                ->isEmpty()
            ->object($this->mock->addNotQuotedColumns(['lib', 'type']))
                ->isIdenticalTo($this->mock)
            ->array($this->mock->getNotQuotedColumns())
                ->isEqualTo([
                    'lib' => true,
                    'type' => true
                ])
        ;
        
        $this->assert('test Actions\AbstractActions::addNotQuotedColumns with a quoted column')
            ->if($this->mock->setQuotedColumns(['used' => true]))
            ->then
            ->exception(function() {
                $this->mock->addNotQuotedColumns(['dateCreate', 'used', 'dateEdit']);
            })
                ->hasCode(\BfwSql\Actions\AbstractActions::ERR_COLUMN_ALREADY_DEFINE_QUOTED)
            ->array($this->mock->getNotQuotedColumns())
                ->isEqualTo([
                    'lib'        => true,
                    'type'       => true,
                    'dateCreate' => true
                ])
        ;
    }
    
    public function testQuoteValue()
    {
        $this->assert('test Actions\AbstractActions::quoteValue - prepare')
            ->if($this->calling($this->sqlConnect->getPDO())->quote = function($str) {
                return '\''.$str.'\'';
            })
        ;
        
        $this->assert('test Actions\AbstractActions::quoteValue - status=none')
            ->if($this->mock->setQuoteStatus(\BfwSql\Actions\AbstractActions::QUOTE_NONE))
            ->then
            ->string($this->mock->quoteValue('lib', 'atoum'))
                ->isEqualTo('atoum')
        ;
        
        $this->assert('test Actions\AbstractActions::quoteValue - status=all')
            ->if($this->mock->setQuoteStatus(\BfwSql\Actions\AbstractActions::QUOTE_ALL))
            ->then
            ->string($this->mock->quoteValue('lib', 'atoum'))
                ->isEqualTo('\'atoum\'')
            ->integer($this->mock->quoteValue('answer', 42))
                ->isEqualTo(42)
        ;
        
        //Test cases : https://github.com/bulton-fr/bfw-sql/issues/41#issuecomment-389154260
        
        $this->assert('test Actions\AbstractActions::quoteValue - status=partial; partial=quoted; quotedColumns and notQuotedColumns empty')
            ->if($this->mock->setQuoteStatus(\BfwSql\Actions\AbstractActions::QUOTE_PARTIALLY))
            ->and($this->mock->setPartiallyPreferedMode(\BfwSql\Actions\AbstractActions::PARTIALLY_MODE_QUOTE))
            ->and($this->mock->setQuotedColumns([]))
            ->and($this->mock->setNotQuotedColumns([]))
            ->then
            
            ->string($this->mock->quoteValue('lib', 'atoum'))
                ->isEqualTo('\'atoum\'')
        ;
        
        $this->assert('test Actions\AbstractActions::quoteValue - status=partial; partial=quoted; quotedColumns empty and into notQuotedColumns')
            ->if($this->mock->setQuoteStatus(\BfwSql\Actions\AbstractActions::QUOTE_PARTIALLY))
            ->and($this->mock->setPartiallyPreferedMode(\BfwSql\Actions\AbstractActions::PARTIALLY_MODE_QUOTE))
            ->and($this->mock->setQuotedColumns([]))
            ->and($this->mock->setNotQuotedColumns(['lib' => true]))
            ->then
            
            ->string($this->mock->quoteValue('lib', 'atoum'))
                ->isEqualTo('atoum')
        ;
        
        $this->assert('test Actions\AbstractActions::quoteValue - status=partial; partial=quoted; into quotedColumns and notQuotedColumns empty')
            ->if($this->mock->setQuoteStatus(\BfwSql\Actions\AbstractActions::QUOTE_PARTIALLY))
            ->and($this->mock->setPartiallyPreferedMode(\BfwSql\Actions\AbstractActions::PARTIALLY_MODE_QUOTE))
            ->and($this->mock->setQuotedColumns(['lib' => true]))
            ->and($this->mock->setNotQuotedColumns([]))
            ->then
            
            ->string($this->mock->quoteValue('lib', 'atoum'))
                ->isEqualTo('\'atoum\'')
        ;
        
        $this->assert('test Actions\AbstractActions::quoteValue - status=partial; partial=quoted; into quotedColumns and notQuotedColumns')
            /*
             * Is not possible with current system.
             * One of the methods addQuotedColumns or addNotQuotedColumns will
             * throw an Exception.
             * 
            ->if($this->mock->setQuoteStatus(\BfwSql\Actions\AbstractActions::QUOTE_PARTIALLY))
            ->and($this->mock->setPartiallyPreferedMode(\BfwSql\Actions\AbstractActions::PARTIALLY_MODE_QUOTE))
            ->and($this->mock->setQuotedColumns(['lib' => true]))
            ->and($this->mock->setNotQuotedColumns(['lib' => true]))
            ->then
            
            ->string($this->mock->quoteValue('lib', 'atoum'))
            */
        ;
        
        $this->assert('test Actions\AbstractActions::quoteValue - status=partial; partial=not-quoted; quotedColumns and notQuotedColumns empty')
            ->if($this->mock->setQuoteStatus(\BfwSql\Actions\AbstractActions::QUOTE_PARTIALLY))
            ->and($this->mock->setPartiallyPreferedMode(\BfwSql\Actions\AbstractActions::PARTIALLY_MODE_NOTQUOTE))
            ->and($this->mock->setQuotedColumns([]))
            ->and($this->mock->setNotQuotedColumns([]))
            ->then
            
            ->string($this->mock->quoteValue('lib', 'atoum'))
                ->isEqualTo('atoum')
        ;
        
        $this->assert('test Actions\AbstractActions::quoteValue - status=partial; partial=not-quoted; quotedColumns empty and into notQuotedColumns')
            ->if($this->mock->setQuoteStatus(\BfwSql\Actions\AbstractActions::QUOTE_PARTIALLY))
            ->and($this->mock->setPartiallyPreferedMode(\BfwSql\Actions\AbstractActions::PARTIALLY_MODE_NOTQUOTE))
            ->and($this->mock->setQuotedColumns([]))
            ->and($this->mock->setNotQuotedColumns(['lib' => true]))
            ->then
            
            ->string($this->mock->quoteValue('lib', 'atoum'))
                ->isEqualTo('atoum')
        ;
        
        $this->assert('test Actions\AbstractActions::quoteValue - status=partial; partial=not-quoted; into quotedColumns and notQuotedColumns empty')
            ->if($this->mock->setQuoteStatus(\BfwSql\Actions\AbstractActions::QUOTE_PARTIALLY))
            ->and($this->mock->setPartiallyPreferedMode(\BfwSql\Actions\AbstractActions::PARTIALLY_MODE_NOTQUOTE))
            ->and($this->mock->setQuotedColumns(['lib' => true]))
            ->and($this->mock->setNotQuotedColumns([]))
            ->then
            
            ->string($this->mock->quoteValue('lib', 'atoum'))
                ->isEqualTo('\'atoum\'')
        ;
        
        $this->assert('test Actions\AbstractActions::quoteValue - status=partial; partial=not-quoted; into quotedColumns and notQuotedColumns')
            /*
             * Is not possible with current system.
             * One of the methods addQuotedColumns or addNotQuotedColumns will
             * throw an Exception.
             * 
            ->if($this->mock->setQuoteStatus(\BfwSql\Actions\AbstractActions::QUOTE_PARTIALLY))
            ->and($this->mock->setPartiallyPreferedMode(\BfwSql\Actions\AbstractActions::PARTIALLY_MODE_NOTQUOTE))
            ->and($this->mock->setQuotedColumns(['lib' => true]))
            ->and($this->mock->setNotQuotedColumns(['lib' => true]))
            ->then
            
            ->string($this->mock->quoteValue('lib', 'atoum'))
            */
        ;
    }
    
    public function testCallObserver()
    {
        $this->assert('test Actions\AbstractActions::callObserver')
            ->given($observer = new \BFW\Test\Helpers\ObserverArray)
            ->given($subjectList = $this->app->getSubjectList())
            ->given($sqlSubject = $subjectList->getSubjectForName('bfw-sql'))
            ->given($sqlSubject->attach($observer))
            ->then
            
            ->variable($this->mock->callObserver())
                ->isNull()
            ->array($observer->getUpdateReceived())
                ->isNotEmpty()
            ->string($observer->getUpdateReceived()[0]->action)
                ->isEqualTo('system query')
            ->object($observer->getUpdateReceived()[0]->context)
                ->isEqualTo($this->mock)
        ;
    }
}