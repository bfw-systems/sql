<?php

namespace BfwSql\Runners\test\unit;

use \atoum;

$vendorPath = realpath(__DIR__.'/../../../../../vendor');
require_once($vendorPath.'/autoload.php');
require_once($vendorPath.'/bulton-fr/bfw/test/unit/helpers/Application.php');
require_once($vendorPath.'/bulton-fr/bfw/test/unit/helpers/ObserverArray.php');
require_once($vendorPath.'/bulton-fr/bfw/test/unit/mocks/src/class/Module.php');

class Observers extends Atoum
{
    use \BfwSql\Test\Helpers\CreateModule;
    
    protected $mock;
    protected $module;
    
    public function beforeTestMethod($testMethod)
    {
        $this->initModule();
        $this->createSqlConnect('myBase');
        
        $this->mockGenerator
            ->makeVisible('addObserver')
            ->makeVisible('checkObserverClass')
            ->makeVisible('checkObserverMonologHandlers')
            ->makeVisible('addMonologForObserver')
            ->generate('BfwSql\Runners\Observers')
        ;
        
        $this->module = $this->app->getModuleList()->getModuleByName('bfw-sql');
        
        if ($testMethod === 'testConstruct') {
            return;
        }
        
        $this->mock = new \mock\BfwSql\Runners\Observers($this->module);
    }
    
    public function testConstruct()
    {
        $this->assert('test Runners\Observers::__construct')
            ->object($this->mock = new \mock\BfwSql\Runners\Observers($this->module))
                ->isInstanceOf('\BfwSql\Runners\Observers')
            ->object($this->mock->getModule())
                ->isIdenticalTo($this->module)
        ;
    }
    
    public function testRun()
    {
        $this->assert('test Runners\Observers::run - prepare')
            ->if($this->calling($this->mock)->addObserver = null)
        ;
        
        $this->assert('test Runners\Observers::run without observer')
            ->variable($this->mock->run())
                ->isNull()
            ->mock($this->mock)
                ->call('addObserver')
                    ->never()
            ->object(
                $subject = \BFW\Application::getInstance()
                    ->getSubjectList()
                    ->getSubjectByName('bfw-sql')
            )
                ->isInstanceOf('\BFW\Subject')
        ;
        
        $this->assert('test Runners\Observers::run with an observer')
            ->given($newobserver = (object) [
                'className'       => '\BfwSql\Observers\Basic',
                'monologHandlers' => (object) [
                    'useGlobal' => true,
                    'others'    => []
                ]
            ])
            ->if($this->module->getConfig()->setConfigKeyForFilename(
                'observers.php',
                'observers',
                [$newobserver]
            ))
            ->then
            
            ->variable($this->mock->run())
                ->isNull()
            ->object(
                $subject = \BFW\Application::getInstance()
                    ->getSubjectList()
                    ->getSubjectByName('bfw-sql')
            )
                ->isInstanceOf('\BFW\Subject')
            ->mock($this->mock)
                ->call('addObserver')
                    ->withArguments($newobserver, $subject)
                        ->once()
        ;
    }
    
    public function testAddObserver()
    {
        $this->assert('test Runners\Observers::addObserver')
            ->given($module = $this->module)
            ->given($subject = new \BFW\Subject)
            ->given($observerInfos = (object) [
                'className'       => '\BfwSql\Observers\Basic',
                'monologHandlers' => (object) [
                    'useGlobal' => true,
                    'others'    => []
                ]
            ])
            ->if($this->calling($this->mock)->checkObserverClass = null)
            ->and($this->calling($this->mock)->checkObserverMonologHandlers = null)
            ->and($this->calling($this->mock)->addMonologForObserver = function(...$args) use ($module) {
                return $module->monolog;
            })
            ->then
            
            ->variable($this->mock->addObserver($observerInfos, $subject))
                ->isNull()
            ->array($observers = $subject->getObservers())
                ->isNotEmpty()
            ->object($observers[0])
                ->isInstanceOf('\BfwSql\Observers\Basic')
            ->mock($this->mock)
                ->call('checkObserverClass')
                    ->withArguments($observerInfos)->once()
                ->call('checkObserverMonologHandlers')
                    ->withArguments($observerInfos)->once()
                ->call('addMonologForObserver')
                    ->withArguments($observerInfos)->once()
        ;
    }
    
    public function testCheckObserverClass()
    {
        $this->assert('test Runners\Observers::checkObserverClass without className property')
            ->exception(function() {
                $this->mock->checkObserverClass(new \stdClass);
            })
                ->hasCode(\BfwSql\Runners\Observers::ERR_ADD_OBSERVER_MISSING_CLASSNAME)
        ;
        
        $this->assert('test Runners\Observers::checkObserverClass with unknown class')
            ->exception(function() {
                $this->mock->checkObserverClass((object) [
                    'className' => '\Test'
                ]);
            })
                ->hasCode(\BfwSql\Runners\Observers::ERR_ADD_OBSERVER_UNKNOWN_CLASS)
        ;
        
        $this->assert('test Runners\Observers::checkObserverClass with unknown class')
            ->variable($this->mock->checkObserverClass((object) [
                    'className' => '\BfwSql\Observers\Basic'
            ]))
                ->isNull()
        ;
    }
    
    public function testCheckObserverMonologHandlers()
    {
        $this->assert('test Runners\Observers::checkObserverMonologHandlers with nothing')
            ->given($observerInfos = new \stdClass)
            ->variable($this->mock->checkObserverMonologHandlers($observerInfos))
                ->isNull()
            ->object($observerInfos->monologHandlers)
                ->isInstanceOf('\stdClass')
            ->boolean($observerInfos->monologHandlers->useGlobal)
                ->isFalse()
            ->array($observerInfos->monologHandlers->others)
                ->isEmpty()
        ;
        
        $this->assert('test Runners\Observers::checkObserverMonologHandlers with bad values')
            ->given($observerInfos = (object) [
                'monologHandlers' => (object) [
                    'useGlobal' => 'false',
                    'others'    => 'none'
                ]
            ])
            ->variable($this->mock->checkObserverMonologHandlers($observerInfos))
                ->isNull()
            ->object($observerInfos->monologHandlers)
                ->isInstanceOf('\stdClass')
            ->boolean($observerInfos->monologHandlers->useGlobal)
                ->isTrue() //It's not an error. Only var type conversion.
            ->array($observerInfos->monologHandlers->others)
                ->isEmpty()
        ;
    }
    
    public function testAddMonologForObserver()
    {
        $this->assert('test Runners\Observers::addMonologForObserver - prepare')
            ->given($observerInfos = (object) [
                'className'       => '\BfwSql\Observers\Basic',
                'monologHandlers' => (object) [
                    'useGlobal' => false,
                    'others'    => [
                        (object) [
                            'name' => '\Monolog\Handler\TestHandler',
                            'args' => []
                        ]
                    ]
                ]
            ])
        ;
        
        $this->assert('test Runners\Observers::addMonologForObserver with new monolog instance')
            ->object($monolog = $this->mock->addMonologForObserver($observerInfos))
                ->isNotIdenticalTo($this->module->monolog)
            ->array($monolog->getHandlers())
                ->size
                    ->isEqualTo(1)
        ;
        
        $this->assert('test Runners\Observers::addMonologForObserver with clone monolog instance')
            ->if($observerInfos->monologHandlers->useGlobal = true)
            ->object($monolog = $this->mock->addMonologForObserver($observerInfos))
                ->isNotIdenticalTo($this->module->monolog)
            ->array($monolog->getHandlers())
                ->size
                    ->isEqualTo(2)
        ;
    }
}