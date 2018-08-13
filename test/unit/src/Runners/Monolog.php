<?php

namespace BfwSql\Runners\test\unit;

use \atoum;

$vendorPath = realpath(__DIR__.'/../../../../vendor');
require_once($vendorPath.'/autoload.php');
require_once($vendorPath.'/bulton-fr/bfw/test/unit/helpers/Application.php');
require_once($vendorPath.'/bulton-fr/bfw/test/unit/helpers/ObserverArray.php');
require_once($vendorPath.'/bulton-fr/bfw/test/unit/mocks/src/Module.php');

class Monolog extends atoum
{
    use \BfwSql\Test\Helpers\CreateModule;
    
    protected $mock;
    protected $module;
    
    public function beforeTestMethod($testMethod)
    {
        $this->initModule();
        $this->createSqlConnect('myBase');
        
        $this->module = $this->app->getModuleList()->getModuleByName('bfw-sql');
        
        if ($testMethod === 'testConstruct') {
            return;
        }
        
        $this->mock = new \mock\BfwSql\Runners\Monolog($this->module);
    }
    
    public function testConstruct()
    {
        $this->assert('test Runners\Monolog::__construct')
            ->object($this->mock = new \mock\BfwSql\Runners\Monolog($this->module))
                ->isInstanceOf('\BfwSql\Runners\Monolog')
            ->object($this->mock->getModule())
                ->isIdenticalTo($this->module)
        ;
    }
    
    public function testRun()
    {
        $this->assert('test Runners\Monolog::run')
            ->if($this->module->getConfig()->setConfigKeyForFilename(
                'monolog.php',
                'handlers',
                [
                    [
                        'name' => '\Monolog\Handler\TestHandler',
                        'args' => []
                    ]
                ]
            ))
            ->variable($this->mock->run())
                ->isNull()
            ->object($this->module->monolog)
                ->isInstanceOf('\BFW\Monolog')
            ->array($handlers = $this->module->monolog->getHandlers())
                ->isNotEmpty()
            ->object($handlers[0])
                ->isInstanceOf('\Monolog\Handler\TestHandler')
        ;
    }
}