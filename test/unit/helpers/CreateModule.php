<?php

namespace BfwSql\Test\Helpers;

$vendorPath = realpath(__DIR__.'/../../../vendor');
require_once($vendorPath.'/bulton-fr/bfw/test/unit/helpers/Application.php');
require_once($vendorPath.'/bulton-fr/bfw/test/unit/mocks/src/class/Module.php');

trait CreateModule
{
    use \BFW\Test\Helpers\Application;
    
    protected $sqlConnect;
    protected $pdo;
    
    protected function initApplication()
    {
        $this->setRootDir(__DIR__.'/../../..');
        $this->createApp();
        $this->initApp();
    }
    
    protected function initModule()
    {
        $this->initApplication();
        $this->generateModule();
        $this->callModuleRunners();
    }
    
    protected function generateModule()
    {
        $this->app->getModuleList()->addModule('bfw-sql');
        
        $module = $this->app->getModuleList()->getModuleByName('bfw-sql');
        $config = $this->declareConfig();
        
        $module->setConfig($config);
        $module->setStatus(true, true);
        
        \BfwSql\UsedClass::getInstance($config);
    }
    
    protected function declareConfig()
    {
        $config = new \BFW\Config('bfw-sql');
        
        $bases = (object) [
            'bases' => []
        ];
        $config->setConfigForFilename('bases.php', $bases);
        
        $class = require(ROOT_DIR.'config/class.php');
        $config->setConfigForFilename('class.php', $class);
        
        $monolog = require(ROOT_DIR.'config/monolog.php');
        $config->setConfigForFilename('monolog.php', $monolog);
        
        $observers = require(ROOT_DIR.'config/observers.php');
        $config->setConfigForFilename('observers.php', $observers);
        
        return $config;
    }
    
    protected function callModuleRunners()
    {
        $module = $this->app->getModuleList()->getModuleByName('bfw-sql');
        
        $runnerMonolog = new \BfwSql\Runners\Monolog($module);
        $runnerMonolog->run();
        
        $runnerObserver = new \BfwSql\Runners\Observers($module);
        $runnerObserver->run();
        
        $runnerDb = new \BfwSql\Runners\ConnectDB($module);
        $runnerDb->run();
    }
    
    protected function createSqlConnect($baseName)
    {
        $baseInfos = (object) [
            'baseKeyName' => $baseName,
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
        ];
        
        $this->sqlConnect = new \BfwSql\Test\Mocks\SqlConnect($baseInfos);
        
        $this->mockGenerator->orphanize('__construct');
        $this->pdo = new \mock\PDO;
        $this->sqlConnect->setPDO($this->pdo);
    }
}
