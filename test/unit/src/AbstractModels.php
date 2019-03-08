<?php

namespace BfwSql\test\unit;

use \atoum;

$vendorPath = realpath(__DIR__.'/../../../vendor');
require_once($vendorPath.'/autoload.php');
require_once($vendorPath.'/bulton-fr/bfw/test/unit/helpers/Application.php');
require_once($vendorPath.'/bulton-fr/bfw/test/unit/mocks/src/Module.php');

class AbstractModels extends atoum
{
    use \BfwSql\Test\Helpers\CreateModule;
    
    protected $mock;
    
    public function beforeTestMethod($testMethod)
    {
        $this->initModule();
        $this->createSqlConnect('myBase');
        
        $this->mockGenerator
            ->makeVisible('obtainApp')
            ->makeVisible('obtainSqlConnect')
            ->orphanize('__construct') //Not call the construct
            ->generate('BfwSql\AbstractModels')
        ;
        
        if ($testMethod === 'testConstructAndGetters') {
            return;
        }
        
        $this->mock = new \mock\BfwSql\AbstractModels;
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
        $this->assert('test AbstractModels::__construct')
            ->given($this->createModule())
            ->and($this->addBase('myBase'))
            ->then
            ->object($obj = new \BfwSql\Test\Helpers\Model)
                ->isInstanceOf('\BfwSql\AbstractModels')
        ;
        
        $this->assert('test AbstractModels::getTableName')
            ->string($obj->getTableName())
                ->isEqualTo('model')
        ;
        
        $this->assert('test AbstractModels::getTableNameWithPrefix')
            ->string($obj->getTableNameWithPrefix())
                ->isEqualTo('test_model')
        ;
        
        $this->assert('test AbstractModels::getAlias')
            ->string($obj->getalias())
                ->isEqualTo('m')
        ;
        
        $this->assert('test AbstractModels::getBaseKeyName')
            ->string($obj->getBaseKeyName())
                ->isEqualTo('myBase')
        ;
    }
    
    public function testObtainApp()
    {
        $this->assert('test AbstractModels::obtainApp')
            ->object($this->invoke($this->mock)->obtainApp())
                ->isInstanceOf('\BFW\Application')
        ;
    }
    
    public function testObtainSqlConnect()
    {
        $this->assert('test AbstractModels::obtainSqlConnect - prepare')
            ->given($this->createModule())
            ->then
            ->if($this->mockGenerator->makeVisible('obtainSqlConnect'))
            ->and($this->mockGenerator->orphanize('__construct'))
            ->given($model = new \mock\BfwSql\Test\Helpers\Model)
        ;
        
        $this->assert('test AbstractModels::obtainSqlConnect without bases')
            ->exception(function() {
                $this->invoke($this->mock)->obtainSqlConnect();
            })
                ->hasCode(\BfwSql\AbstractModels::ERR_NO_CONNECTION_CONFIGURED)
        ;
        
        $this->assert('test AbstractModels::obtainSqlConnect - prepare - adding multiple bases')
            ->if($this->addBase('myBase'))
            ->and($this->addBase('myBase2'))
        ;
        
        $this->assert('test AbstractModels::obtainSqlConnect with many bases and without baseKeyName declared')
            ->exception(function() {
                $this->invoke($this->mock)->obtainSqlConnect();
            })
                ->hasCode(\BfwSql\AbstractModels::ERR_NEED_BASEKEYNAME_DEFINED)
        ;
        
        $this->assert('test AbstractModels::obtainSqlConnect with many bases and unknow baseKeyName declared')
            ->if($model->setBaseKeyName('myBase1'))
            ->then
            ->exception(function() use ($model) {
                $this->invoke($model)->obtainSqlConnect();
            })
                ->hasCode(\BfwSql\AbstractModels::ERR_UNKNOWN_CONNECTION_FOR_BASEKEYNAME)
        ;
        
        $this->assert('test AbstractModels::obtainSqlConnect with many bases and correct baseKeyName declared')
            ->if($model->setBaseKeyName('myBase'))
            ->then
            ->object($this->invoke($model)->obtainSqlConnect())
                ->isInstanceOf('\BfwSql\SqlConnect')
        ;
        
        $this->assert('test AbstractModels::obtainSqlConnect with one base')
            ->if($model->setBaseKeyName('myBase'))
            ->and($this->app->getModuleList()->getModuleByName('bfw-sql')->listBases = [])
            ->and($this->addBase('myBase'))
            ->then
            ->object($this->invoke($model)->obtainSqlConnect())
                ->isInstanceOf('\BfwSql\SqlConnect')
        ;
    }
    
    protected function queriesPrepare($queryType)
    {
        $this->app
            ->getModuleList()
            ->getModuleByName('bfw-sql')
            ->getConfig()
            ->setConfigKeyForFilename('class.php', 'QueriesDelete', '\mock\BfwSql\Queries\Delete')
            ->setConfigKeyForFilename('class.php', 'QueriesInsert', '\mock\BfwSql\Queries\Insert')
            ->setConfigKeyForFilename('class.php', 'QueriesSelect', '\mock\BfwSql\Queries\Select')
            ->setConfigKeyForFilename('class.php', 'QueriesUpdate', '\mock\BfwSql\Queries\Update')
        ;
        
        $this->assert('test AbstractModels::'.$queryType.' - prepare')
            ->and($this->addBase('myBase'))
            ->given($model = new \mock\BfwSql\Test\Helpers\Model)
            ->then
            ->given($setSqlConnect = function($sqlConnect) {
                $this->sqlConnect = $sqlConnect;
            })
            ->and($setSqlConnect->call($model, $this->sqlConnect))
        ;
        
        return $model;
    }
    
    public function testSelect()
    {
        $this->assert('test AbstractModels::select')
            ->given($model = $this->queriesPrepare('select'))
            ->then
            ->object($select = $model->select())
                ->isInstanceOf('\BfwSql\Queries\Select')
            ->string($select->getQueriesParts()['table']->getName())
                ->isEqualTo('test_model')
            ->string($select->getQueriesParts()['table']->getShortcut())
                ->isEqualto('m')
        ;
    }
    
    public function testInsert()
    {
        $this->assert('test AbstractModels::insert')
            ->given($model = $this->queriesPrepare('insert'))
            ->then
            ->object($select = $model->insert())
                ->isInstanceOf('\BfwSql\Queries\Insert')
            ->string($select->getQueriesParts()['table']->getName())
                ->isEqualTo('test_model')
            ->string($select->getQueriesParts()['table']->getShortcut())
                ->isEqualto('m')
        ;
    }
    
    public function testUpdate()
    {
        $this->assert('test AbstractModels::update')
            ->given($model = $this->queriesPrepare('update'))
            ->then
            ->object($select = $model->update())
                ->isInstanceOf('\BfwSql\Queries\Update')
            ->string($select->getQueriesParts()['table']->getName())
                ->isEqualTo('test_model')
            ->string($select->getQueriesParts()['table']->getShortcut())
                ->isEqualto('m')
        ;
    }
    
    public function testDelete()
    {
        $this->assert('test AbstractModels::delete')
            ->given($model = $this->queriesPrepare('delete'))
            ->then
            ->object($select = $model->delete())
                ->isInstanceOf('\BfwSql\Queries\Delete')
            ->string($select->getQueriesParts()['table']->getName())
                ->isEqualTo('test_model')
            ->string($select->getQueriesParts()['table']->getShortcut())
                ->isEqualto('m')
        ;
    }
}