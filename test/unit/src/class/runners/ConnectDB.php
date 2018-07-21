<?php

namespace BfwSql\Runners\test\unit;

use \atoum;

$vendorPath = realpath(__DIR__.'/../../../../../vendor');
require_once($vendorPath.'/autoload.php');
require_once($vendorPath.'/bulton-fr/bfw/test/unit/helpers/Application.php');
require_once($vendorPath.'/bulton-fr/bfw/test/unit/helpers/ObserverArray.php');
require_once($vendorPath.'/bulton-fr/bfw/test/unit/mocks/src/class/Module.php');

class ConnectDB extends Atoum
{
    use \BfwSql\Test\Helpers\CreateModule;
    
    protected $mock;
    protected $module;
    
    public function beforeTestMethod($testMethod)
    {
        $this->initModule();
        $this->createSqlConnect('myBase');
        
        $this->mockGenerator
            ->makeVisible('connectToDatabase')
            ->generate('BfwSql\Runners\ConnectDB')
        ;
        
        $this->module = $this->app->getModuleForName('bfw-sql');
        
        if ($testMethod === 'testConstruct') {
            return;
        }
        
        $this->mock = new \mock\BfwSql\Runners\ConnectDB($this->module);
    }
    
    public function testConstruct()
    {
        $this->assert('test Runners\ConnectDB::__construct')
            ->object($this->mock = new \mock\BfwSql\Runners\ConnectDB($this->module))
                ->isInstanceOf('\BfwSql\Runners\ConnectDB')
            ->object($this->mock->getModule())
                ->isIdenticalTo($this->module)
        ;
    }
    
    public function testRun()
    {
        $this->assert('test Runners\ConnectDB::run - prepare')
            ->if($this->calling($this->mock)->connectToDatabase = null)
        ;
        
        $this->assert('test Runners\ConnectDB::run without base')
            ->variable($this->mock->run())
                ->isNull()
            ->array($this->module->listBases)
                ->isEmpty()
            ->mock($this->mock)
                ->call('connectToDatabase')
                    ->never()
        ;
        
        $this->assert('test Runners\ConnectDB::run with base')
            ->given($config = $this->module->getConfig())
            ->given($newBase = (object) [
                'baseKeyName' => 'myBase',
                'filePath'    => '',
                'host'        => 'localhost',
                'port'        => 3306,
                'baseName'    => 'atoum',
                'user'        => 'atoum',
                'password'    => '',
                'baseType'    => 'mysql',
                'pdoOptions'  => [],
                'useUtf8'     => true,
                'tablePrefix' => 'test_'
            ])
            ->if($config->setConfigKeyForFile('bases.php', 'bases', [$newBase]))
            ->then
            ->variable($this->mock->run())
                ->isNull()
            ->array($this->module->listBases)
                ->isEmpty()
            ->mock($this->mock)
                ->call('connectToDatabase')
                    ->withArguments($newBase)
                        ->once()
        ;
    }
    
    public function testConnectToDatabase()
    {
        $this->assert('test Runners\ConnectDB::connectToDatabase without baseType')
            ->exception(function() {
                $this->mock->connectToDatabase((object) [
                    'baseKeyName' => '',
                    'filePath'    => '',
                    'host'        => '',
                    'port'        => 0,
                    'baseName'    => '',
                    'user'        => '',
                    'password'    => '',
                    'baseType'    => '',
                    'pdoOptions'  => [],
                    'useUtf8'     => true,
                    'tablePrefix' => ''
                ]);
            })
                ->hasCode(\BfwSql\Runners\ConnectDB::ERR_NO_BASE_TYPE)
        ;
        
        $this->assert('test Runners\ConnectDB::connectToDatabase without baseName when many database')
            ->if($this->module->getConfig()->setConfigKeyForFile(
                'bases.php',
                'bases',
                [
                    (object) [
                        'baseKeyName' => '',
                        'filePath'    => '',
                        'host'        => 'localhost',
                        'port'        => 3306,
                        'baseName'    => 'atoum',
                        'user'        => 'atoum',
                        'password'    => '',
                        'baseType'    => 'mysql',
                        'pdoOptions'  => [],
                        'useUtf8'     => true,
                        'tablePrefix' => 'test_'
                    ],
                    (object) [
                        'baseKeyName' => 'myBase',
                        'filePath'    => '',
                        'host'        => 'localhost',
                        'port'        => 3306,
                        'baseName'    => 'atoum',
                        'user'        => 'atoum',
                        'password'    => '',
                        'baseType'    => 'mysql',
                        'pdoOptions'  => [],
                        'useUtf8'     => true,
                        'tablePrefix' => 'test_'
                    ]
                ]
            ))
            ->exception(function() {
                $this->mock->connectToDatabase((object) [
                    'baseKeyName' => '',
                    'filePath'    => '',
                    'host'        => 'localhost',
                    'port'        => 3306,
                    'baseName'    => 'atoum',
                    'user'        => 'atoum',
                    'password'    => '',
                    'baseType'    => 'mysql',
                    'pdoOptions'  => [],
                    'useUtf8'     => true,
                    'tablePrefix' => 'test_'
                ]);
            })
                ->hasCode(\BfwSql\Runners\ConnectDB::ERR_NO_CONNECTION_KEYNAME)
        ;
        
        $this->assert('test Runners\ConnectDB::connectToDatabase with correct database')
            ->if($this->module->getConfig()->setConfigKeyForFile(
                'class.php',
                'SqlConnect',
                '\mock\BfwSql\SqlConnect'
            ))
            ->and($this->module->getConfig()->setConfigKeyForFile(
                'bases.php',
                'bases',
                [
                    (object) [
                        'baseKeyName' => 'myBase',
                        'filePath'    => '',
                        'host'        => 'localhost',
                        'port'        => 3306,
                        'baseName'    => 'atoum',
                        'user'        => 'atoum',
                        'password'    => '',
                        'baseType'    => 'mysql',
                        'pdoOptions'  => [],
                        'useUtf8'     => true,
                        'tablePrefix' => 'test_'
                    ]
                ]
            ))
            ->and(
                $this->mockGenerator
                ->orphanize('createConnection')
                ->generate('BfwSql\SqlConnect')
            )
            ->then
            
            ->variable($this->mock->run()) //To have listBases = []
            ->array($this->module->listBases)
                ->isNotEmpty()
            ->object($this->module->listBases['myBase'])
                ->isInstanceOf('\mock\BfwSql\SqlConnect')
        ;
    }
}