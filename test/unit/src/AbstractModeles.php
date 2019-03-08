<?php

namespace BfwSql\test\unit;

use \atoum;

$vendorPath = realpath(__DIR__.'/../../../vendor');
require_once($vendorPath.'/autoload.php');
require_once($vendorPath.'/bulton-fr/bfw/test/unit/helpers/Application.php');
require_once($vendorPath.'/bulton-fr/bfw/test/unit/mocks/src/Module.php');

class AbstractModeles extends atoum
{
    use \BFW\Test\Helpers\Application;
    
    protected $mock;
    
    public function beforeTestMethod($testMethod)
    {
        $this->setRootDir(__DIR__.'/../../..');
        $this->createApp();
        $this->initApp();
        
        $this->mockGenerator
            ->makeVisible('getApp')
            ->makeVisible('obtainSqlConnect')
            ->orphanize('__construct') //Not call the construct
            ->generate('BfwSql\AbstractModeles')
        ;
        
        if ($testMethod === 'testConstructAndGetters') {
            return;
        }
        
        $this->mock = new \mock\BfwSql\AbstractModeles;
    }
    
    protected function createModule()
    {
        $this->app->getModuleList()->addModule('bfw-sql');
        
        $module = $this->app->getModuleList()->getModuleByName('bfw-sql');
        $config = new \BFW\Config('bfw-sql');
        
        $module->setConfig($config);
        $module->setStatus(true, true);
        
        $module->listBases = [];
    }
    
    protected function addBase($baseName)
    {
        $module    = $this->app->getModuleList()->getModuleByName('bfw-sql');
        $baseInfos = $baseInfos = new class ($baseName) {
            public $baseKeyName   = '';
            public $filePath      = '';
            public $host          = 'localhost';
            public $port          = 3306;
            public $baseName      = 'atoum';
            public $user          = 'atoum';
            public $password      = '';
            public $baseType      = 'mysql';
            public $encoding      = '';
            public $tablePrefix   = 'test_';
            public $pdoOptions    = [];
            public $pdoAttributes = [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION
            ];
            
            public function __construct($baseKeyName)
            {
                $this->baseKeyName = $baseKeyName;
            }
        };
        
        $module->listBases[$baseName] = new \BfwSql\SqlConnect($baseInfos);
    }
    
    public function testConstructAndGetters()
    {
        $this->assert('test AbstractModeles::__construct')
            ->given($this->createModule())
            ->and($this->addBase('myBase'))
            ->then
            ->object($obj = new \BfwSql\Test\Helpers\Modele)
                ->isInstanceOf('\BfwSql\AbstractModeles')
        ;
        
        $this->assert('test AbstractModeles::getTableName')
            ->string($obj->getTableName())
                ->isEqualTo('modele')
        ;
        
        $this->assert('test AbstractModeles::getTableNameWithPrefix')
            ->string($obj->getTableNameWithPrefix())
                ->isEqualTo('test_modele')
        ;
        
        $this->assert('test AbstractModeles::getBaseKeyName')
            ->string($obj->getBaseKeyName())
                ->isEqualTo('myBase')
        ;
    }
    
    public function testObtainApp()
    {
        $this->assert('test AbstractModeles::obtainApp')
            ->object($this->invoke($this->mock)->obtainApp())
                ->isInstanceOf('\BFW\Application')
        ;
    }
    
    public function testObtainSqlConnect()
    {
        $this->assert('test AbstractModeles::obtainSqlConnect - prepare')
            ->given($this->createModule())
            ->then
            ->if($this->mockGenerator->makeVisible('obtainSqlConnect'))
            ->and($this->mockGenerator->orphanize('__construct'))
            ->given($modele = new \mock\BfwSql\Test\Helpers\Modele)
        ;
        
        $this->assert('test AbstractModeles::obtainSqlConnect without bases')
            ->exception(function() {
                $this->invoke($this->mock)->obtainSqlConnect();
            })
                ->hasCode(\BfwSql\AbstractModeles::ERR_NO_CONNECTION_CONFIGURED)
        ;
        
        $this->assert('test AbstractModeles::obtainSqlConnect - prepare - adding multiple bases')
            ->if($this->addBase('myBase'))
            ->and($this->addBase('myBase2'))
        ;
        
        $this->assert('test AbstractModeles::obtainSqlConnect with many bases and without baseKeyName declared')
            ->exception(function() {
                $this->invoke($this->mock)->obtainSqlConnect();
            })
                ->hasCode(\BfwSql\AbstractModeles::ERR_NEED_BASEKEYNAME_DEFINED)
        ;
        
        $this->assert('test AbstractModeles::obtainSqlConnect with many bases and unknow baseKeyName declared')
            ->if($modele->setBaseKeyName('myBase1'))
            ->then
            ->exception(function() use ($modele) {
                $this->invoke($modele)->obtainSqlConnect();
            })
                ->hasCode(\BfwSql\AbstractModeles::ERR_UNKNOWN_CONNECTION_FOR_BASEKEYNAME)
        ;
        
        $this->assert('test AbstractModeles::obtainSqlConnect with many bases and correct baseKeyName declared')
            ->if($modele->setBaseKeyName('myBase'))
            ->then
            ->object($this->invoke($modele)->obtainSqlConnect())
                ->isInstanceOf('\BfwSql\SqlConnect')
        ;
        
        $this->assert('test AbstractModeles::obtainSqlConnect with one base')
            ->if($modele->setBaseKeyName('myBase'))
            ->and($this->app->getModuleList()->getModuleByName('bfw-sql')->listBases = [])
            ->and($this->addBase('myBase'))
            ->then
            ->object($this->invoke($modele)->obtainSqlConnect())
                ->isInstanceOf('\BfwSql\SqlConnect')
        ;
    }
}