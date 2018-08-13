<?php

namespace BfwSql\test\unit;

use \atoum;

$vendorPath = realpath(__DIR__.'/../../../vendor');
require_once($vendorPath.'/autoload.php');
require_once($vendorPath.'/bulton-fr/bfw/test/unit/helpers/Application.php');
require_once($vendorPath.'/bulton-fr/bfw/test/unit/helpers/ObserverArray.php');
require_once($vendorPath.'/bulton-fr/bfw/test/unit/mocks/src/Module.php');

class Sql extends atoum
{
    use \BfwSql\Test\Helpers\CreateModule;
    
    protected $mock;
    
    public function beforeTestMethod($testMethod)
    {
        $this->initModule();
        $this->createSqlConnect('myBase');
        
        if ($testMethod === 'testConstructAndGetters') {
            return;
        }
        
        $this->mock = new \mock\BfwSql\Sql($this->sqlConnect);
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
    
    public function testConstructAndGetters()
    {
        $this->assert('test Sql::__construct')
            ->object($obj = new \mock\BfwSql\Sql($this->sqlConnect))
                ->isInstanceOf('\BfwSql\Sql')
        ;
        
        $this->assert('test Sql::getSqlConnect')
            ->object($obj->getSqlConnect())
                ->isIdenticalTo($this->sqlConnect)
        ;
        
        $this->assert('test Sql::getPrefix')
            ->string($obj->getPrefix())
                ->isEqualTo('test_')
        ;
    }
    
    public function testObtainLastInsertedId()
    {
        $this->assert('test Sql::obtainLastInsertedId without sequence name')
            ->if($this->calling($this->pdo)->lastInsertId = '123')
            ->then
            ->integer($this->mock->obtainLastInsertedId())
                ->isEqualTo(123)
            ->mock($this->pdo)
                ->call('lastInsertId')
                    ->withArguments(null)
                        ->once()
        ;
        
        $this->assert('test Sql::obtainLastInsertedId with a sequence name')
            ->if($this->calling($this->pdo)->lastInsertId = '124')
            ->then
            ->integer($this->mock->obtainLastInsertedId('tableId'))
                ->isEqualTo(124)
            ->mock($this->pdo)
                ->call('lastInsertId')
                    ->withArguments('tableId')
                        ->once()
        ;
    }
    
    public function testObtainLastInsertedIdWithoutAI()
    {
        $this->assert('test Sql::obtainLastInsertedIdWithoutAI - prepare')
            ->given($mockStatements = $this->disablePdoRequest())
            ->if($this->calling($mockStatements->prepareReturn)->execute = null)
            ->and($this->calling($mockStatements->prepareReturn)->fetch = false)
            ->and($this->calling($mockStatements->prepareReturn)->closeCursor = null)
            ->and($this->calling($this->pdo)->errorInfo = [
                0 => '00000',
                1 => null,
                2 => null
            ])
            ->then
            ->given($select = null)
            ->given($mock = $this->mock)
            ->and($this->calling($this->mock)->select = function($type = 'array') use ($mock, &$select) {
                $select = new \mock\BfwSql\Queries\Select(
                    $mock->getSqlConnect(),
                    $type
                );
                
                return $select;
            })
        ;
        
        $this->assert('test Sql::obtainLastInsertedIdWithoutAI with a simple order, without where, with a result')
            ->if($this->calling($mockStatements->prepareReturn)->fetch = function() {
                return ['id' => 123];
            })
            ->then
            ->integer($this->mock->obtainLastInsertedIdWithoutAI(
                'myTable',
                'id',
                ['id' => 'DESC']
            ))
                ->isEqualTo(123)
            ->array($select->getQueriesParts()['where']->getList())
                ->isEmpty()
            ->string($select->getQueriesParts()['order']->getList()[0]->generate())
                ->isEqualTo('`id` DESC')
        ;
        
        $this->assert('test Sql::obtainLastInsertedIdWithoutAI with a complex order, without where, with a result')
            ->if($this->calling($mockStatements->prepareReturn)->fetch = function() {
                return ['id' => 124];
            })
            ->then
            ->integer($this->mock->obtainLastInsertedIdWithoutAI(
                'myTable',
                'id',
                ['id' => 'DESC', 'label' => 'ASC']
            ))
                ->isEqualTo(124)
            ->array($select->getQueriesParts()['where']->getList())
                ->isEmpty()
            ->string($select->getQueriesParts()['order']->getList()[0]->generate())
                ->isEqualTo('`id` DESC')
            ->string($select->getQueriesParts()['order']->getList()[1]->generate())
                ->isEqualTo('`label` ASC')
        ;
        
        $this->assert('test Sql::obtainLastInsertedIdWithoutAI with a simple order and where, with a result')
            ->if($this->calling($mockStatements->prepareReturn)->fetch = function() {
                return ['id' => 125];
            })
            ->then
            ->integer($this->mock->obtainLastInsertedIdWithoutAI(
                'myTable',
                'id',
                ['id' => 'DESC'],
                'label="atoum"'
            ))
                ->isEqualTo(125)
            ->string($select->getQueriesParts()['where']->getList()[0])
                ->isEqualTo('label="atoum"')
            ->string($select->getQueriesParts()['order']->getList()[0]->generate())
                ->isEqualTo('`id` DESC')
        ;
        
        $this->assert('test Sql::obtainLastInsertedIdWithoutAI with a simple order and complex where, with a result')
            ->if($this->calling($mockStatements->prepareReturn)->fetch = function() {
                return ['id' => 125];
            })
            ->then
            ->integer($this->mock->obtainLastInsertedIdWithoutAI(
                'myTable',
                'id',
                ['id' => 'DESC'],
                ['label="atoum"', 'type="unit-test"']
            ))
                ->isEqualTo(125)
            ->string($select->getQueriesParts()['where']->getList()[0])
                ->isEqualTo('label="atoum"')
            ->string($select->getQueriesParts()['where']->getList()[1])
                ->isEqualTo('type="unit-test"')
            ->string($select->getQueriesParts()['order']->getList()[0]->generate())
                ->isEqualTo('`id` DESC')
        ;
        
        $this->assert('test Sql::obtainLastInsertedIdWithoutAI without a result')
            ->if($this->calling($mockStatements->prepareReturn)->fetch = false)
            ->then
            ->integer($this->mock->obtainLastInsertedIdWithoutAI(
                'myTable',
                'id',
                ['id' => 'DESC']
            ))
                ->isEqualTo(0)
        ;
    }
    
    public function testSelect()
    {
        $this->app
            ->getModuleList()
            ->getModuleByName('bfw-sql')
            ->getConfig()
            ->setConfigKeyForFilename(
                'class.php',
                'QueriesSelect',
                '\mock\BfwSql\Queries\Select'
            )
        ;
        
        $this->assert('test Sql::select without argument')
            ->object($select = $this->mock->select())
                ->isInstanceOf('\BfwSql\Queries\Select')
            ->mock($select)
                ->call('__construct')
                    ->withArguments($this->mock->getSqlConnect(), 'array')
                    ->once()
        ;
        
        $this->assert('test Sql::select with "array" argument')
            ->object($select = $this->mock->select('array'))
                ->isInstanceOf('\BfwSql\Queries\Select')
            ->mock($select)
                ->call('__construct')
                    ->withArguments($this->mock->getSqlConnect(), 'array')
                    ->once()
        ;
        
        $this->assert('test Sql::select with "object" argument')
            ->object($select = $this->mock->select('object'))
                ->isInstanceOf('\BfwSql\Queries\Select')
            ->mock($select)
                ->call('__construct')
                    ->withArguments($this->mock->getSqlConnect(), 'object')
                    ->once()
        ;
    }
    
    public function testInsert()
    {
        $this->app
            ->getModuleList()
            ->getModuleByName('bfw-sql')
            ->getConfig()
            ->setConfigKeyForFilename(
                'class.php',
                'QueriesInsert',
                '\mock\BfwSql\Queries\Insert'
            )
        ;
        
        $this->assert('test Sql::insert with default arguments')
            ->object($insert = $this->mock->insert())
                ->isInstanceOf('\BfwSql\Queries\Insert')
            ->mock($insert)
                ->call('__construct')
                    ->withArguments(
                        $this->mock->getSqlConnect(),
                        \BfwSql\Helpers\Quoting::QUOTE_ALL
                    )
                    ->once()
        ;
        
        $this->assert('test Sql::insert with all arguments')
            ->object($insert = $this->mock->insert(
                \BfwSql\Helpers\Quoting::QUOTE_PARTIALLY
            ))
                ->isInstanceOf('\BfwSql\Queries\Insert')
            ->mock($insert)
                ->call('__construct')
                    ->withArguments(
                        $this->mock->getSqlConnect(),
                        \BfwSql\Helpers\Quoting::QUOTE_PARTIALLY
                    )
                    ->once()
        ;
    }
    
    public function testUpdate()
    {
        $this->app
            ->getModuleList()
            ->getModuleByName('bfw-sql')
            ->getConfig()
            ->setConfigKeyForFilename(
                'class.php',
                'QueriesUpdate',
                '\mock\BfwSql\Queries\Update'
            )
        ;
        
        $this->assert('test Sql::update with default arguments')
            ->object($update = $this->mock->update())
                ->isInstanceOf('\BfwSql\Queries\Update')
            ->mock($update)
                ->call('__construct')
                    ->withArguments(
                        $this->mock->getSqlConnect(),
                        \BfwSql\Helpers\Quoting::QUOTE_ALL
                    )
                    ->once()
        ;
        
        $this->assert('test Sql::update with all arguments')
            ->object($update = $this->mock->update(
                \BfwSql\Helpers\Quoting::QUOTE_PARTIALLY
            ))
                ->isInstanceOf('\BfwSql\Queries\Update')
            ->mock($update)
                ->call('__construct')
                    ->withArguments(
                        $this->mock->getSqlConnect(),
                        \BfwSql\Helpers\Quoting::QUOTE_PARTIALLY
                    )
                    ->once()
        ;
    }
    
    public function testDelete()
    {
        $this->app
            ->getModuleList()
            ->getModuleByName('bfw-sql')
            ->getConfig()
            ->setConfigKeyForFilename(
                'class.php',
                'QueriesDelete',
                '\mock\BfwSql\Queries\Delete'
            )
        ;
        
        $this->assert('test Sql::delete')
            ->object($delete = $this->mock->delete())
                ->isInstanceOf('\BfwSql\Queries\Delete')
            ->mock($delete)
                ->call('__construct')
                    ->withArguments($this->mock->getSqlConnect())
                    ->once()
        ;
    }
    
    public function testCreateID()
    {
        $this->assert('test Sql::createId - prepare')
            ->given($mockStatements = $this->disablePdoRequest())
            ->if($this->calling($mockStatements->prepareReturn)->execute = null)
            ->and($this->calling($mockStatements->prepareReturn)->fetch = false)
            ->and($this->calling($this->pdo)->errorInfo = [
                0 => '00000',
                1 => null,
                2 => null
            ])
            ->then
            ->given($select = null)
            ->given($mock = $this->mock)
            ->and($this->calling($this->mock)->select = function($type = 'array') use ($mock, &$select) {
                $select = new \mock\BfwSql\Queries\Select(
                    $mock->getSqlConnect(),
                    $type
                );
                
                return $select;
            })
        ;
        
        $this->assert('test Sql::createId without existing line')
            ->integer($this->mock->createId('myTable', 'id'))
                ->isEqualTo(1)
        ;
        
        $this->assert('test Sql::createId with the first line have id=3')
            ->if($this->calling($mockStatements->prepareReturn)->fetch = function() {
                return ['id' => 3];
            })
            ->then
            ->integer($this->mock->createId('myTable', 'id'))
                ->isEqualTo(2)
        ;
        
        $this->assert('test Sql::createId with only one line who have id=1')
            ->if($this->calling($mockStatements->prepareReturn)->fetch[1] = function() {
                return ['id' => 1];
            })
            ->and($this->calling($mockStatements->prepareReturn)->fetch[2] = function() {
                return ['id' => 1];
            })
            ->then
            ->integer($this->mock->createId('myTable', 'id'))
                ->isEqualTo(2)
        ;
        
        $this->assert('test Sql::createId with many line from id 1 to 41')
            ->if($this->calling($mockStatements->prepareReturn)->fetch[1] = function() {
                return ['id' => 1];
            })
            ->and($this->calling($mockStatements->prepareReturn)->fetch[2] = function() {
                return ['id' => 41];
            })
            ->then
            ->integer($this->mock->createId('myTable', 'id'))
                ->isEqualTo(42)
        ;
    }
    
    public function testQuery()
    {
        $this->assert('test Sql::query - prepare - adding observer')
            ->given($observer = new \BFW\Test\Helpers\ObserverArray)
            ->given($subjectList = $this->app->getSubjectList())
            ->given($sqlSubject = $subjectList->getSubjectByName('bfw-sql'))
            ->given($sqlSubject->attach($observer))
        ;
        
        $this->assert('test Sql::query with an error')
            ->if($this->calling($this->pdo)->query = false)
            ->and($this->calling($this->pdo)->errorInfo = function() {
                //From http://php.net/manual/en/pdostatement.errorinfo.php
                return [
                    0 => '42S02',
                    1 => -204,
                    2 => '[IBM][CLI Driver][DB2/LINUX] SQL0204N  "DANIELS.BONES" is an undefined name.  SQLSTATE=42704'
                ];
            })
            ->then
            
            ->exception(function() {
                $this->mock->query('SELECT id FROM myTable');
            })
                ->hasCode(\BfwSql\Sql::ERR_QUERY_BAD_REQUEST)
                ->hasMessage('[IBM][CLI Driver][DB2/LINUX] SQL0204N  "DANIELS.BONES" is an undefined name.  SQLSTATE=42704')
            ->integer($this->mock->getSqlConnect()->getNbQuery())
                ->isEqualTo(1)
            ->array($observer->getUpdateReceived())
                ->isNotEmpty()
            ->string($observer->getUpdateReceived()[0]->action)
                ->isEqualTo('user query')
            ->object($observer->getUpdateReceived()[0]->context)
            ->string($observer->getUpdateReceived()[0]->context->request)
                ->isEqualTo('SELECT id FROM myTable')
            ->array($observer->getUpdateReceived()[0]->context->error)
                ->isEqualTo([
                    0 => '42S02',
                    1 => -204,
                    2 => '[IBM][CLI Driver][DB2/LINUX] SQL0204N  "DANIELS.BONES" is an undefined name.  SQLSTATE=42704'
                ])
        ;
        
        $this->assert('test Sql::query without error')
            ->if($this->calling($this->pdo)->query = new \mock\PDOStatement)
            ->and($this->calling($this->pdo)->errorInfo = function() {
                //From http://php.net/manual/en/pdostatement.errorinfo.php
                return [
                    0 => '00000',
                    1 => null,
                    2 => null
                ];
            })
            ->then
            
            ->object($this->mock->query('SELECT id FROM myTable'))
                ->isInstanceOf('\PDOStatement')
            ->integer($this->mock->getSqlConnect()->getNbQuery())
                ->isEqualTo(2)
            ->array($observer->getUpdateReceived())
                ->isNotEmpty()
            ->string($observer->getUpdateReceived()[1]->action)
                ->isEqualTo('user query')
            ->object($observer->getUpdateReceived()[1]->context)
            ->string($observer->getUpdateReceived()[1]->context->request)
                ->isEqualTo('SELECT id FROM myTable')
            ->array($observer->getUpdateReceived()[1]->context->error)
                ->isEqualTo([
                    0 => '00000',
                    1 => null,
                    2 => null
                ])
        ;
    }
}