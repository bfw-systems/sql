<?php

namespace BfwSql\Queries\Parts\test\unit;

use \atoum;

$vendorPath = realpath(__DIR__.'/../../../../../vendor');
require_once($vendorPath.'/autoload.php');

class SubQuery extends atoum
{
    use \BfwSql\Test\Helpers\CreateModule;
    
    protected $mock;
    protected $query;
    
    public function beforeTestMethod($testMethod)
    {
        $this->initModule();
        $this->createSqlConnect('myBase');
        
        $this->query = new \mock\BfwSql\Queries\AbstractQuery($this->sqlConnect);
        
        $this->mockGenerator->makeVisible('obtainAssembledQuery');
        
        if ($testMethod === 'testObtainAssembledQuery') {
            $this->mockGenerator->orphanize('__construct');
        }
        
        $this->mockGenerator->generate('BfwSql\Queries\Parts\SubQuery');
    }
    
    public function testConstructAndGetter()
    {
        $this->assert('test Queries\Parts\SubQuery::__construct and getters with string query')
            ->object($this->mock = new \mock\BfwSql\Queries\Parts\SubQuery(
                $this->query,
                'nbSessionIp',
                'SELECT COUNT(id) FROM sessions s WHERE s.ip=u.lastIp'
            ))
                ->isInstanceOf('\BfwSql\Queries\Parts\SubQuery')
            ->string($this->mock->getQuery())
                ->isEqualTo('SELECT COUNT(id) FROM sessions s WHERE s.ip=u.lastIp')
            ->string($this->mock->getShortcut())
                ->isEqualTo('nbSessionIp')
        ;
        
        $this->assert('test Queries\Parts\SubQuery::__construct and getters with AbstractQuery query')
            ->given($query = new \mock\BfwSql\Queries\Select($this->sqlConnect, 'object'))
            ->and($query->from(['s' => 'sessions'], 'COUNT(id)'))
            ->and($query->where('s.ip=u.lastIp'))
            ->then
            ->object($this->mock = new \mock\BfwSql\Queries\Parts\SubQuery(
                $this->query,
                'nbSessionIp',
                $query
            ))
                ->isInstanceOf('\BfwSql\Queries\Parts\SubQuery')
            ->string($this->mock->getQuery())
                ->isEqualTo(
                    'SELECT COUNT(id)'."\n"
                    .'FROM `test_sessions` AS `s`'."\n"
                    .'WHERE s.ip=u.lastIp'."\n"
                )
            ->string($this->mock->getShortcut())
                ->isEqualTo('nbSessionIp')
        ;
    }
    
    public function testObtainAssembledQuery()
    {
        $this->assert('test Queries\Parts\SubQuery::obtainAssembledQuery - prepare')
            ->given($this->mock = new \mock\BfwSql\Queries\Parts\SubQuery($this->query, 'nbSessionIp', ''))
        ;
        
        $this->assert('test Queries\Parts\SubQuery::obtainAssembledQuery with string query')
            ->string($this->mock->obtainAssembledQuery('SELECT COUNT(id) FROM sessions s WHERE s.ip=u.lastIp'))
                ->isEqualTo('SELECT COUNT(id) FROM sessions s WHERE s.ip=u.lastIp')
        ;
        
        $this->assert('test Queries\Parts\SubQuery::obtainAssembledQuery with AbstractQuery query')
            ->given($query = new \mock\BfwSql\Queries\Select($this->sqlConnect, 'object'))
            ->and($query->from(['s' => 'sessions'], 'COUNT(id)'))
            ->and($query->where('s.ip=u.lastIp'))
            ->then
            ->string($this->mock->obtainAssembledQuery($query))
                ->isEqualTo(
                    'SELECT COUNT(id)'."\n"
                    .'FROM `test_sessions` AS `s`'."\n"
                    .'WHERE s.ip=u.lastIp'."\n"
                )
        ;
        
        $this->assert('test Queries\Parts\SubQuery::obtainAssembledQuery with not object or string')
            ->exception(function() {
                $this->mock->obtainAssembledQuery(42);
            })
                ->hasCode(\BfwSql\Queries\Parts\SubQuery::ERR_QUERY_NOT_OBJECT_OR_STRING)
        ;
        
        $this->assert('test Queries\Parts\SubQuery::obtainAssembledQuery with bad object instance')
            ->exception(function() {
                $this->mock->obtainAssembledQuery(new \stdClass);
            })
                ->hasCode(\BfwSql\Queries\Parts\SubQuery::ERR_QUERY_OBJECT_BAD_INSTANCE)
        ;
    }
    
    public function testGenerate()
    {
        $this->assert('test Queries\Parts\Order::generate')
            ->if($this->mock = new \mock\BfwSql\Queries\Parts\SubQuery(
                $this->query, 
                'nbSessionIp',
                'SELECT COUNT(id) FROM sessions s WHERE s.ip=u.lastIp'
            ))
            ->then
            ->string($this->mock->generate())
                ->isEqualTo('(SELECT COUNT(id) FROM sessions s WHERE s.ip=u.lastIp) AS `nbSessionIp`')
        ;
    }
}