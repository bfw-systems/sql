<?php

namespace BfwSql\Runners\test\unit;

use \atoum;

$vendorPath = realpath(__DIR__.'/../../../../vendor');
require_once($vendorPath.'/autoload.php');
require_once($vendorPath.'/bulton-fr/bfw/test/unit/helpers/Application.php');
require_once($vendorPath.'/bulton-fr/bfw/test/unit/helpers/ObserverArray.php');
require_once($vendorPath.'/bulton-fr/bfw/test/unit/mocks/src/Module.php');

class ConnectDB extends atoum
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
        
        $this->module = $this->app->getModuleList()->getModuleByName('bfw-sql');
        
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
            ->given($newBase = new class {
                public $baseKeyName   = 'myBase';
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
            })
            ->if($config->setConfigKeyForFilename('bases.php', 'bases', [$newBase]))
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
                $this->mock->connectToDatabase(new class {
                    public $baseKeyName   = '';
                    public $filePath      = '';
                    public $host          = '';
                    public $port          = 0;
                    public $baseName      = '';
                    public $user          = '';
                    public $password      = '';
                    public $baseType      = '';
                    public $encoding      = 'utf8';
                    public $tablePrefix   = '';
                    public $pdoOptions    = [];
                    public $pdoAttributes = [
                        \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION
                    ];
                });
            })
                ->hasCode(\BfwSql\Runners\ConnectDB::ERR_NO_BASE_TYPE)
        ;
        
        $this->assert('test Runners\ConnectDB::connectToDatabase without baseName when many database')
            ->if($this->module->getConfig()->setConfigKeyForFilename(
                'bases.php',
                'bases',
                [
                    new class {
                        public $baseKeyName   = '';
                        public $filePath      = '';
                        public $host          = 'localhost';
                        public $port          = 3306;
                        public $baseName      = 'atoum';
                        public $user          = 'atoum';
                        public $password      = '';
                        public $baseType      = 'mysql';
                        public $encoding      = 'utf8';
                        public $tablePrefix   = 'test_';
                        public $pdoOptions    = [];
                        public $pdoAttributes = [
                            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION
                        ];
                    },
                    new class {
                        public $baseKeyName   = 'myBase';
                        public $filePath      = '';
                        public $host          = 'localhost';
                        public $port          = 3306;
                        public $baseName      = 'atoum';
                        public $user          = 'atoum';
                        public $password      = '';
                        public $baseType      = 'mysql';
                        public $encoding      = 'utf8';
                        public $tablePrefix   = 'test_';
                        public $pdoOptions    = [];
                        public $pdoAttributes = [
                            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION
                        ];
                    }
                ]
            ))
            ->exception(function() {
                $this->mock->connectToDatabase(new class {
                    public $baseKeyName   = '';
                    public $filePath      = '';
                    public $host          = 'localhost';
                    public $port          = 3306;
                    public $baseName      = 'atoum';
                    public $user          = 'atoum';
                    public $password      = '';
                    public $baseType      = 'mysql';
                    public $encoding      = 'utf8';
                    public $tablePrefix   = 'test_';
                    public $pdoOptions    = [];
                    public $pdoAttributes = [
                        \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION
                    ];
                });
            })
                ->hasCode(\BfwSql\Runners\ConnectDB::ERR_NO_CONNECTION_KEYNAME)
        ;
        
        $this->assert('test Runners\ConnectDB::connectToDatabase with correct database')
            ->if($this->module->getConfig()->setConfigKeyForFilename(
                'class.php',
                'SqlConnect',
                '\mock\BfwSql\SqlConnect'
            ))
            ->and($this->module->getConfig()->setConfigKeyForFilename(
                'bases.php',
                'bases',
                [
                    new class {
                        public $baseKeyName   = 'myBase';
                        public $filePath      = '';
                        public $host          = 'localhost';
                        public $port          = 3306;
                        public $baseName      = 'atoum';
                        public $user          = 'atoum';
                        public $password      = '';
                        public $baseType      = 'mysql';
                        public $encoding      = 'utf8';
                        public $tablePrefix   = 'test_';
                        public $pdoOptions    = [];
                        public $pdoAttributes = [
                            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION
                        ];
                    }
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