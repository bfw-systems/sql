<?php

namespace BfwSql\Queries\Parts\test\unit;

use \Atoum;

$vendorPath = realpath(__DIR__.'/../../../../../vendor');
require_once($vendorPath.'/autoload.php');

class SubQueryList extends atoum
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
        
        $this->mock = new \mock\BfwSql\Queries\Parts\SubQueryList($this->query);
    }
    
    public function testConstruct()
    {
        $this->assert('test Queries\Parts\SubQueryList::__construct')
            ->object($this->mock = new \mock\BfwSql\Queries\Parts\SubQueryList($this->query))
                ->isInstanceOf('\BfwSql\Queries\Parts\SubQueryList')
                ->isInstanceOf('\Iterator')
                ->isInstanceOf('\BfwSql\Queries\Parts\AbstractList')
                ->isInstanceOf('\BfwSql\Queries\Parts\AbstractPart')
                ->isInstanceOf('\BfwSql\Queries\Parts\PartInterface')
            ->string($this->mock->getSeparator())
                ->isEqualTo(',')
        ;
    }
    
    public function testInvoke()
    {
        $this->assert('test Queries\Parts\SubQueryList::__invoke')
            ->variable($this->mock->__invoke(
                'nbSessionIp',
                'SELECT COUNT(id) FROM sessions s WHERE s.ip=u.lastIp'
            ))
                ->isNull()
            ->object($item = $this->mock->getList()[0])
                ->isInstanceOf('\BfwSql\Queries\Parts\SubQuery')
                ->string($item->getShortcut())
                    ->isEqualTo('nbSessionIp')
                ->string($item->getQuery())
                    ->isEqualTo('SELECT COUNT(id) FROM sessions s WHERE s.ip=u.lastIp')
        ;
    }
    
    public function testGenerate()
    {
        $this->assert('test Queries\Parts\SubQueryList::generate without item')
            ->string($this->mock->generate())
                ->isEmpty()
        ;
        
        $this->assert('test Queries\Parts\SubQueryList::generate with one item')
            ->if($this->mock->__invoke(
                'nbSessionIp',
                'SELECT COUNT(id) FROM sessions s WHERE s.ip=u.lastIp'
            ))
            ->string($this->mock->generate())
                ->isEqualTo('(SELECT COUNT(id) FROM sessions s WHERE s.ip=u.lastIp) AS `nbSessionIp`')
        ;
        
        $this->assert('test Queries\Parts\SubQueryList::generate with many items')
            ->if($this->mock->__invoke(
                'nbItems',
                'SELECT COUNT(id) FROM items i WHERE i.iduser=u.iduser'
            ))
            ->string($this->mock->generate())
                ->isEqualTo(
                    '(SELECT COUNT(id) FROM sessions s WHERE s.ip=u.lastIp) AS `nbSessionIp`,'
                    .'(SELECT COUNT(id) FROM items i WHERE i.iduser=u.iduser) AS `nbItems`'
                )
        ;
    }
}