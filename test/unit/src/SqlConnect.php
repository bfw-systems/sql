<?php

namespace BfwSql\test\unit;

use \atoum;

$vendorPath = realpath(__DIR__.'/../../../vendor');
require_once($vendorPath.'/autoload.php');
require_once($vendorPath.'/bulton-fr/bfw/test/unit/helpers/ObserverArray.php');

class SqlConnect extends atoum
{
    use \BfwSql\Test\Helpers\CreateModule;
    
    protected $mock;
    protected $baseInfos;
    
    public function beforeTestMethod($testMethod)
    {
        $this->initModule();
        
        $this->mockGenerator
            ->orphanize('__construct')
            ->generate('PDO')
        ;
        $this->app
            ->getModuleList()
            ->getModuleByName('bfw-sql')
            ->getConfig()
            ->setConfigKeyForFilename('class.php', 'PDO', '\mock\PDO')
        ;
        
        $this->mockGenerator
            ->makeVisible('useUtf8')
            ->generate('BfwSql\SqlConnect')
        ;
        
        $this->baseInfos = $baseInfos = new class {
            public $baseKeyName = 'myBase';
            public $filePath    = '';
            public $host        = 'localhost';
            public $port        = 3306;
            public $baseName    = 'atoum';
            public $user        = 'atoum';
            public $password    = '';
            public $baseType    = 'mysql';
            public $pdoOptions  = [];
            public $useUtf8     = false;
            public $tablePrefix = 'test_';
        };
        
        if ($testMethod === 'testConstructAndGetters') {
            return;
        }
        
        $this->mock = new \mock\BfwSql\SqlConnect($this->baseInfos);
    }
    
    public function testConstructAndGetters()
    {
        $this->assert('test SqlConnect::__construct')
            ->object($this->mock = new \mock\BfwSql\SqlConnect($this->baseInfos))
                ->isInstanceOf('\BfwSql\SqlConnect')
        ;
        
        $this->assert('test SqlConnect::getConnectionInfos')
            ->object($this->mock->getConnectionInfos())
                ->isIdenticalTo($this->baseInfos)
        ;
        
        $this->assert('test SqlConnect::getType')
            ->string($this->mock->getType())
                ->isEqualTo('mysql')
        ;
    }
    
    public function testCreateConnection()
    {
        $this->assert('test SqlConnect::createConnection - prepare')
            ->if($this->calling($this->mock)->useUtf8 = null)
        ;
        
        $this->assert('test SqlConnect::createConnection with non-existent dsn')
            ->if($this->baseInfos->baseType = 'fake')
            ->then
            ->exception(function() {
                $this->mock->createConnection();
            })
                ->hasCode(\BfwSql\SqlConnect::ERR_DSN_METHOD_NOT_FOUND)
        ;
        
        $this->assert('test SqlConnect::createConnection with existing dsn and without utf8')
            ->if($this->baseInfos->baseType = 'mysql')
            ->then
            ->variable($this->mock->createConnection())
                ->isNull()
            ->object($this->mock->getPDO())
                ->isInstanceOf('\PDO')
            ->mock($this->mock)
                ->call('useUtf8')
                    ->never()
        ;
        
        $this->assert('test SqlConnect::createConnection with existing dsn and with utf8')
            ->if($this->baseInfos->useUtf8 = true)
            ->then
            ->variable($this->mock->createConnection())
                ->isNull()
            ->object($this->mock->getPDO())
                ->isInstanceOf('\PDO')
            ->mock($this->mock)
                ->call('useUtf8')
                    ->once()
        ;
    }
    
    public function testUseUtf8()
    {
        $this->assert('test SqlConnect::useUtf8')
            ->if($this->mock->createConnection()) //To have a pdo instance
            ->and($this->calling($this->mock->getPDO())->exec = null)
            ->then
            ->variable($this->mock->useUtf8())
                ->isNull()
            ->mock($this->mock->getPDO())
                ->call('exec')
                    ->withArguments('SET NAMES utf8')
                    ->once()
        ;
    }
    
    public function testProtect()
    {
        $this->assert('test SqlConnect::protect')
            ->if($this->mock->createConnection()) //To have a pdo instance
            ->and($this->calling($this->mock->getPDO())->quote = function($string) {
                return '\''.$string.'\'';
            })
            ->then
            ->string($this->mock->protect('atoum'))
                ->isEqualTo('atoum') //Check there is no quote at begin or end of the string
            ->mock($this->mock->getPDO())
                ->call('quote')
                    ->withArguments('atoum')
                    ->once()
            ;
        ;
    }
    
    public function testGetAndUpNbQuery()
    {
        $this->assert('test SqlConnect::getNbQuery and SqlConnect::upNbQuery')
            ->integer($this->mock->getNbQuery())
                ->isEqualTo(0)
            ->variable($this->mock->upNbQuery())
                ->isNull()
            ->integer($this->mock->getNbQuery())
                ->isEqualTo(1)
        ;
    }
}