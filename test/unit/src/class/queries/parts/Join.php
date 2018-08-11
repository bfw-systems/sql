<?php

namespace BfwSql\Queries\Parts\test\unit;

use \Atoum;

$vendorPath = realpath(__DIR__.'/../../../../../../vendor');
require_once($vendorPath.'/autoload.php');

class Join extends Atoum
{
    use \BfwSql\Test\Helpers\CreateModule;
    
    protected $mock;
    protected $query;
    
    public function beforeTestMethod($testMethod)
    {
        $this->initModule();
        $this->createSqlConnect('myBase');
        
        $this->query = new \mock\BfwSql\Queries\AbstractQuery($this->sqlConnect);
        
        if ($testMethod === 'testConstructAndGetter') {
            return;
        }
        
        $this->mock = new \mock\BfwSql\Queries\Parts\Join($this->query);
    }
    
    public function testConstructAndGetter()
    {
        $this->assert('test Queries\Parts\Join::__construct')
            ->object($this->mock = new \mock\BfwSql\Queries\Parts\Join($this->query))
                ->isInstanceOf('\BfwSql\Queries\Parts\Join')
                ->isInstanceOf('\BfwSql\Queries\Parts\Table')
                ->isInstanceOf('\BfwSql\Queries\Parts\AbstractPart')
                ->isInstanceOf('\BfwSql\Queries\Parts\PartInterface')
            ->string($this->mock->getOn())
                ->isEmpty()
        ;
    }
    
    public function testInvoke()
    {
        $this->assert('test Queries\Parts\Join::__invoke')
            ->variable($this->mock->__invoke(
                ['a' => 'access'],
                'name',
                'a.id_access=u.id_access'
            ))
                ->isNull()
            //--- Begin Test call to parent::invoke() ---
            ->string($this->mock->getName())
                ->isEqualto('test_access')
            ->string($this->mock->getShortcut())
                ->isEqualTo('a')
            ->array($this->mock->getColumns()->getList())
                ->isNotEmpty()
            //--- End Test call to parent::invoke() ---
            ->string($this->mock->getOn())
                ->isEqualTo('a.id_access=u.id_access')
        ;
    }
    
    public function testGenerate()
    {
        $this->assert('test Queries\Parts\Join::generate without shortcut')
            ->if($this->mock->__invoke('access', 'name', 'test_access.id_access=u.id_access'))
            ->then
            ->string($this->mock->generate())
                ->isEqualTo('`test_access` ON test_access.id_access=u.id_access')
        ;
        
        $this->assert('test Queries\Parts\Join::generate with shortcut')
            ->if($this->mock->__invoke(['a' => 'access'], 'name', 'a.id_access=u.id_access'))
            ->then
            ->string($this->mock->generate())
                ->isEqualTo('`test_access` AS `a` ON a.id_access=u.id_access')
        ;
    }
}