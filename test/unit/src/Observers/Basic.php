<?php

namespace BfwSql\Observers\test\unit;

use \atoum;

$vendorPath = realpath(__DIR__.'/../../../../vendor');
require_once($vendorPath.'/autoload.php');
require_once($vendorPath.'/bulton-fr/bfw/test/unit/helpers/Application.php');
require_once($vendorPath.'/bulton-fr/bfw/test/unit/helpers/ObserverArray.php');
require_once($vendorPath.'/bulton-fr/bfw/test/unit/mocks/src/Module.php');
require_once($vendorPath.'/bulton-fr/bfw/test/unit/mocks/src/Subject.php');

class Basic extends atoum
{
    use \BfwSql\Test\Helpers\CreateModule;
    
    protected $mock;
    protected $monolog;
    
    public function beforeTestMethod($testMethod)
    {
        $this->initModule();
        $this->createSqlConnect('myBase');
        
        $this->mockGenerator
            ->makeVisible('haveMonologHandler')
            ->makeVisible('analyzeUpdate')
            ->makeVisible('userQuery')
            ->makeVisible('systemQuery')
            ->makeVisible('addQueryToMonoLog')
            ->generate('BfwSql\Observers\Basic')
        ;
        
        $this->monolog = $this->app->getModuleList()->getModuleByName('bfw-sql')->monolog;
        
        if ($testMethod !== 'testHaveMonologHandler') {
            $this->addMonologTestHandler();
        }
        
        if ($testMethod === 'testConstruct') {
            return;
        }
        
        $this->mock = new \mock\BfwSql\Observers\Basic($this->monolog);
    }
    
    protected function addMonologTestHandler()
    {
        $this->monolog->addNewHandler([
            'name' => '\Monolog\Handler\TestHandler',
            'args' => []
        ]);
    }
    
    public function testConstruct()
    {
        $this->assert('test Observers\Basic::__construct')
            ->object($this->mock = new \mock\BfwSql\Observers\Basic($this->monolog))
                ->isInstanceOf('\BfwSql\Observers\Basic')
                ->isInstanceOf('\SplObserver')
            ->object($this->mock->getMonolog())
                ->isIdenticalTo($this->monolog)
            ->string($this->mock->getAction())
                ->isEmpty()
            ->variable($this->mock->getContext())
                ->isNull()
        ;
    }
    
    public function testHaveMonologHandler()
    {
        $this->assert('test Observers\Basic::haveMonologHandler without handler')
            ->array($this->monolog->getHandlers())
                ->size->isEqualTo(1)
            ->boolean($this->mock->haveMonologHandler())
                ->isTrue()
        ;
        
        $this->assert('test Observers\Basic::haveMonologHandler with an handler')
            ->if($this->addMonologTestHandler())
            ->array($this->monolog->getHandlers())
                ->size->isGreaterThan(1)
            ->boolean($this->mock->haveMonologHandler())
                ->isTrue()
        ;
    }
    
    public function testUpdate()
    {
        $this->assert('test Observers\Basic::update - prepare')
            ->if($this->calling($this->mock)->analyzeUpdate = null)
            ->given($subject = new \BFW\Test\Mock\Subject)
        ;
        
        $this->assert('test Observers\Basic::update without handlers')
            ->if($this->calling($this->mock)->haveMonologHandler = false)
            ->then
            ->variable($this->mock->update($subject))
                ->isNull()
            ->mock($this->mock)
                ->call('analyzeUpdate')
                    ->never()
        ;
        
        $this->assert('test Observers\Basic::update without handlers')
            ->if($this->calling($this->mock)->haveMonologHandler = true)
            ->and($subject->setAction('user query'))
            ->and($context = new class {
                public $request = 'SELECT * FROM users';
                public $error = null;
            })
            ->and($subject->setContext($context))
            ->then
            ->variable($this->mock->update($subject))
                ->isNull()
            ->string($this->mock->getAction())
                ->isEqualTo('user query')
            ->object($this->mock->getContext())
                ->isIdenticalTo($context)
            ->mock($this->mock)
                ->call('analyzeUpdate')
                    ->once()
        ;
    }
    
    public function testAnalyzeUpdate()
    {
        $this->assert('test Observers\Basic::analyzeUpdate - prepare')
            ->if($this->calling($this->mock)->haveMonologHandler = true)
            ->and($this->calling($this->mock)->userQuery = null)
            ->and($this->calling($this->mock)->systemQuery = null)
            ->given($subject = new \BFW\Test\Mock\Subject)
            ->and($subject->setContext(null))
        ;
        
        $this->assert('test Observers\Basic::analyzeUpdate with user query')
            ->and($subject->setAction('user query'))
            ->then
            ->variable($this->mock->update($subject))
            ->mock($this->mock)
                ->call('userQuery')
                    ->once()
                ->call('systemQuery')
                    ->never()
        ;
        
        $this->assert('test Observers\Basic::analyzeUpdate with system query')
            ->and($subject->setAction('system query'))
            ->then
            ->variable($this->mock->update($subject))
            ->mock($this->mock)
                ->call('userQuery')
                    ->never()
                ->call('systemQuery')
                    ->once()
        ;
        
        $this->assert('test Observers\Basic::analyzeUpdate with unknown')
            ->and($subject->setAction('my query'))
            ->then
            ->variable($this->mock->update($subject))
            ->mock($this->mock)
                ->call('userQuery')
                    ->never()
                ->call('systemQuery')
                    ->never()
        ;
    }
    
    public function testUserQuery()
    {
        $this->assert('test Observers\Basic::userQuery')
            ->if($this->calling($this->mock)->haveMonologHandler = true)
            ->and($this->calling($this->mock)->addQueryToMonoLog = null)
            ->then
            
            ->given($subject = new \BFW\Test\Mock\Subject)
            ->given($context = new class () {
                public $request = '';
                public $error = [];
                
                public function __construct()
                {
                    $this->request = 'SELECT * FROM users';
                    $this->error   = [
                        0 => '00000',
                        1 => null,
                        2 => null
                    ];
                }
            })
            ->and($subject->setAction('user query'))
            ->and($subject->setContext($context))
            ->then
            
            ->variable($this->mock->update($subject))
            ->mock($this->mock)
                ->call('addQueryToMonoLog')
                    ->withArguments(
                        'SELECT * FROM users',
                        [
                            0 => '00000',
                            1 => null,
                            2 => null
                        ]
                    )
                        ->once()
        ;
    }
    
    public function testSystemQuery()
    {
        $this->assert('test Observers\Basic::systemQuery - prepare')
            ->if($this->calling($this->mock)->haveMonologHandler = true)
            ->and($this->calling($this->mock)->addQueryToMonoLog = null)
            ->then
            
            ->given($query = new \BfwSql\Queries\Select($this->sqlConnect, 'object'))
            ->if($query->from('users', '*'))
            ->and($query->assemble())
            ->given($setLastErrorInfos = function($lastErrorInfos) {
                $this->lastErrorInfos = $lastErrorInfos;
            })
            ->and($setLastErrorInfos->call(
                $query->getExecuter(),
                [
                    0 => '00000',
                    1 => null,
                    2 => null
                ]
            ))
            
            ->given($subject = new \BFW\Test\Mock\Subject)
            ->and($subject->setAction('system query'))
            ->and($subject->setContext($query->getExecuter()))
            ->then
        ;
        
        $this->assert('test Observers\Basic::systemQuery with AbstractQueries in context')
            ->variable($this->mock->update($subject))
            ->mock($this->mock)
                ->call('addQueryToMonoLog')
                    ->withArguments(
                        'SELECT `test_users`.*'."\n".'FROM `test_users`'."\n",
                        [
                            0 => '00000',
                            1 => null,
                            2 => null
                        ]
                    )
                        ->once()
        ;
        
        $this->assert('test Observers\Basic::systemQuery with incorrect context instance')
            ->if($subject->setContext(new \stdClass))
            ->then
            ->exception(function() use ($subject) {
                $this->mock->update($subject);
            })
                ->hasCode(\BfwSql\Observers\Basic::ERR_SYSTEM_QUERY_CONTEXT_CLASS)
        ;
    }
    
    public function testAddQueryToMonoLog()
    {
        $this->assert('test Observers\Basic::addQueryToMonoLog')
            ->given($monologHandler = $this->monolog->getHandlers()[0])
            ->then
            
            ->given($subject = new \BFW\Test\Mock\Subject)
            ->given($context = new class () {
                public $request = '';
                public $error = [];
                
                public function __construct()
                {
                    $this->request = 'SELECT * FROM users';
                    $this->error   = [
                        0 => '00000',
                        1 => null,
                        2 => null
                    ];
                }
            })
            ->and($subject->setAction('user query'))
            ->and($subject->setContext($context))
            ->then
            
            ->variable($this->mock->update($subject))
                ->isNull()
            ->boolean($monologHandler->hasDebugRecords())
                ->isTrue()
            ->array($records = $monologHandler->getRecords())
                ->isNotEmpty()
            ->variable($records[0]['level'])
                ->isEqualTo(\Monolog\Logger::DEBUG)
            ->given($datetime = $records[0]['datetime'])
            ->string($records[0]['formatted'])
                ->isEqualTo(
                    '['.$datetime->format('Y-m-d H:i:s').'] bfw-sql.DEBUG: '
                    .'Type: user query ; Query: SELECT * FROM users ; '
                    .'Errors: Array (     [0] => 00000     [1] =>      [2] =>  )'
                    .'  [] []'
                    ."\n"
                )
        ;
    }
}