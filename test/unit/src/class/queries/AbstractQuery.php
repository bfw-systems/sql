<?php

namespace BfwSql\Queries\test\unit;

use \atoum;

$vendorPath = realpath(__DIR__.'/../../../../../vendor');
require_once($vendorPath.'/autoload.php');
require_once($vendorPath.'/bulton-fr/bfw/test/unit/helpers/Application.php');
require_once($vendorPath.'/bulton-fr/bfw/test/unit/helpers/ObserverArray.php');
require_once($vendorPath.'/bulton-fr/bfw/test/unit/mocks/src/Module.php');

class AbstractQuery extends atoum
{
    use \BfwSql\Test\Helpers\CreateModule;
    
    protected $mock;
    
    public function beforeTestMethod($testMethod)
    {
        $this->initModule();
        $this->createSqlConnect('myBase');
        
        $this->mockGenerator
            ->makeVisible('defineQueriesParts')
            ->makeVisible('assembleRequest')
            ->makeVisible('obtainGenerateOrder')
            ->makeVisible('assembleRequestPart')
            ->makeVisible('addMissingKeysToPartInfos')
            ->makeVisible('obtainPartInfosDefaultValues')
            ->generate('BfwSql\Queries\AbstractQuery')
        ;
        
        if ($testMethod === 'testConstructAndGetters') {
            return;
        }
        
        $this->mock = new \mock\BfwSql\Queries\AbstractQuery($this->sqlConnect);
    }
    
    public function testConstructAndGetters()
    {
        $this->assert('test Queries\AbstractQuery::__construct')
            ->object($this->mock = new \mock\BfwSql\Queries\AbstractQuery($this->sqlConnect))
                ->isInstanceOf('\BfwSql\Queries\AbstractQuery')
            ->object($this->mock->getSqlConnect())
                ->isIdenticalTo($this->sqlConnect)
            ->object($this->mock->getExecuter())
                ->isInstanceOf('\BfwSql\Executers\Common')
            ->array($this->mock->getQueriesParts())
                ->hasSize(2)
                ->hasKeys(['table', 'where'])
            ->mock($this->mock)
                ->call('defineQueriesParts')
                    ->once()
        ;
    }
    
    public function testDefineQueriesParts()
    {
        $this->assert('Queries\AbstractQuery::defineQueriesParts')
            ->array($this->mock->getQueriesParts())
                ->hasSize(2)
                ->hasKeys(['table', 'where'])
                ->object($this->mock->getQueriesParts()['table'])
                    ->isInstanceOf('\BfwSql\Queries\Parts\Table')
                ->object($this->mock->getQueriesParts()['where'])
                    ->isInstanceOf('\BfwSql\Queries\Parts\WhereList')
        ;
    }
    
    public function testCall()
    {
        $this->assert('Queries\AbstractQuery::__call - prepare')
            ->given($addToQueriesParts = function($name, $part) {
                $this->queriesParts[$name] = $part;
            })
        ;
        
        $this->assert('Queries\AbstractQuery::__call with unknown query part')
            ->exception(function() {
                $this->mock->__call('atoum', []);
            })
                ->hasCode(\BfwSql\Queries\AbstractQuery::ERR_CALL_UNKNOWN_METHOD)
        ;
        
        $this->assert('Queries\AbstractQuery::__call with object which not have __invoke')
            ->given($select = new \BfwSql\Queries\Select($this->sqlConnect, 'object'))
            ->if($addToQueriesParts->call($this->mock, 'select', $select))
            ->then
            ->object($this->mock->__call('select', []))
                ->isIdenticalTo($select)
        ;
        
        $this->assert('Queries\AbstractQuery::__call with object which have __invoke')
            ->given($table = new \mock\BfwSql\Queries\Parts\Table($this->mock))
            ->if($addToQueriesParts->call($this->mock, 'table', $table)) //Replace by the mock
            ->then
            ->object($this->mock->__call('table', ['myTable']))
                ->isIdenticalTo($this->mock)
            ->mock($table)
                ->call('__invoke')
                    ->withArguments('myTable')
                        ->once()
        ;
    }
    
    public function testGetAssembledRequest()
    {
        $this->assert('test Queries\AbstractQuery::getAssembledRequest for default value')
            ->string($this->mock->getAssembledRequest())
                ->isEmpty()
        ;
    }
    
    public function testGetPreparedParams()
    {
        $this->assert('test Queries\AbstractQuery::getPreparedParams for default value')
            ->array($this->mock->getPreparedParams())
                ->isEmpty()
        ;
    }
    
    public function testIsAssembled()
    {
        $this->assert('test Queries\AbstractQuery::isAssembled for default value')
            ->boolean($this->mock->isAssembled())
                ->isFalse()
        ;
        
        $this->assert('test Queries\AbstractQuery::isAssembled with an assembled request')
            ->if($this->mock->query('SELECT id FROM test'))
            ->then
            ->boolean($this->mock->isAssembled())
                ->isTrue()
        ;
    }
    
    public function testObtainPartInfosDefaultValues()
    {
        $this->assert('Queries\AbstractQuery::obtainPartInfosDefaultValues - prepare')
            ->given($addToQueriesParts = function($name, $part) {
                $this->queriesParts[$name] = $part;
            })
        ;
        
        $this->assert('Queries\AbstractQuery::obtainPartInfosDefaultValues with AbstractPart object')
            ->object($this->mock->obtainPartInfosDefaultValues('table'))
                ->isInstanceOf('\BfwSql\Queries\Parts\AbstractPart')
                ->isIdenticalTo($this->mock->getQueriesParts()['table'])
        ;
        
        $this->assert('Queries\AbstractQuery::obtainPartInfosDefaultValues with AbstractPart object')
            ->given($select = new \BfwSql\Queries\Select($this->sqlConnect, 'object'))
            ->if($addToQueriesParts->call($this->mock, 'select', $select))
            ->then
            ->object($partInfos = $this->mock->obtainPartInfosDefaultValues('select'))
                ->isInstanceOf('\BfwSql\Queries\Parts\AbstractPart')
            ->string(get_class($partInfos))
                ->contains('class@anonymous')
            ->string($partInfos->generate())
                ->isEmpty()
        ;
    }
    
    public function testAddMissingKeysToPartInfos()
    {
        $this->assert('Queries\AbstractQuery::addMissingKeysToPartInfos with nothing')
            ->given($partInfos = [])
            ->variable($this->mock->addMissingKeysToPartInfos('atoum', $partInfos))
                ->isNull()
            ->array($partInfos)
                ->hasKeys(['callback', 'prefix', 'usePartPrefix', 'canBeEmpty'])
            ->array($partInfos['callback'])
                ->object($partInfos['callback'][0])
                    ->isInstanceOf('\BfwSql\Queries\Parts\AbstractPart')
                ->string($partInfos['callback'][1])
                    ->isEqualTo('generate')
            ->string($partInfos['prefix'])
                ->isEmpty()
            ->boolean($partInfos['usePartPrefix'])
                ->isTrue()
            ->boolean($partInfos['canBeEmpty'])
                ->isTrue()
        ;
        
        $this->assert('Queries\AbstractQuery::addMissingKeysToPartInfos with all')
            ->given($partInfos = [
                'callback'      => [$this, 'generate'],
                'prefix'        => 'TEST',
                'usePartPrefix' => false,
                'canBeEmpty'    => false
            ])
            ->variable($this->mock->addMissingKeysToPartInfos('atoum', $partInfos))
                ->isNull()
            ->array($partInfos)
                ->hasKeys(['callback', 'prefix', 'usePartPrefix', 'canBeEmpty'])
            ->array($partInfos['callback'])
                ->isEqualTo([$this, 'generate'])
            ->string($partInfos['prefix'])
                ->isEqualTo('TEST')
            ->boolean($partInfos['usePartPrefix'])
                ->isFalse()
            ->boolean($partInfos['canBeEmpty'])
                ->isFalse()
        ;
    }
    
    public function testAssembleRequestPart()
    {
        $this->assert('Queries\AbstractQuery::assembleRequestPart with data to generate')
            ->if($this->mock->table('myTable'))
            ->then
            ->string($this->mock->assembleRequestPart(
                'table',
                ['usePartPrefix' => false]
            ))
                ->isEqualTo('`test_myTable`')
        ;
        
        $this->assert('Queries\AbstractQuery::assembleRequestPart with data to generate and prefix')
            ->if($this->mock->table('myTable'))
            ->then
            ->string($this->mock->assembleRequestPart(
                'table',
                [
                    'prefix'        => 'FROM',
                    'usePartPrefix' => true
                ]
            ))
                ->isEqualTo('FROM `test_myTable`')
        ;
        
        $this->assert('Queries\AbstractQuery::assembleRequestPart without data but can be empty')
            ->string($this->mock->assembleRequestPart('where', []))
                ->isEmpty()
        ;
        
        $this->assert('Queries\AbstractQuery::assembleRequestPart without data but can not be empty')
            ->exception(function() {
                $this->mock->assembleRequestPart(
                    'where',
                    ['canBeEmpty' => false]
                );
            })
                ->hasCode(\BfwSql\Queries\AbstractQuery::ERR_ASSEMBLE_EMPTY_PART)
        ;
    }
    
    public function testAssembleRequest()
    {
        $this->assert('Queries\AbstractQuery::assembleRequest - prepare')
            ->if($this->calling($this->mock)->obtainGenerateOrder = function() {
                return [
                    'table' => [
                        'prefix'     => 'FROM',
                        'canBeEmpty' => false
                    ],
                    'where' => []
                ];
            })
        ;
        
        $this->assert('Queries\AbstractQuery::assembleRequest without table name')
            ->exception(function() {
                $this->mock->assembleRequest();
            })
                ->hasCode(\BfwSql\Queries\AbstractQuery::ERR_ASSEMBLE_MISSING_TABLE_NAME)
        ;
        
        $this->assert('Queries\AbstractQuery::assembleRequest with only from')
            ->if($this->mock->table(['t' => 'myTable']))
            ->then
            ->variable($this->mock->assembleRequest())
                ->isNull()
            ->string($this->mock->getAssembledRequest())
                ->isEqualTo('FROM `test_myTable` AS `t`'."\n")
        ;
        
        $this->assert('Queries\AbstractQuery::assembleRequest with from and where')
            ->if($this->mock->where('`t`.`name`="atoum"'))
            ->then
            ->variable($this->mock->assembleRequest())
                ->isNull()
            ->string($this->mock->getAssembledRequest())
                ->isEqualTo(
                    'FROM `test_myTable` AS `t`'."\n"
                    .'WHERE `t`.`name`="atoum"'."\n"
                )
        ;
    }
    
    public function testAssemble()
    {
        $this->assert('test Queries\AbstractQuery::assemble - prepare')
            ->given($setAssembledRequest = function($assembledRequest) {
                $this->assembledRequest = $assembledRequest;
            })
            ->if($this->mock->table('myTable'))
            ->and($this->calling($this->mock)->obtainGenerateOrder = function() {
                return [
                    'table' => [
                        'prefix'     => 'FROM',
                        'canBeEmpty' => false
                    ],
                    'where' => []
                ];
            })
        ;
        
        $this->assert('test Queries\AbstractQuery::assemble when request is not assembled and not forced')
            ->string($this->mock->assemble())
                ->isEqualTo('FROM `test_myTable`'."\n")
            ->mock($this->mock)
                ->call('isAssembled')
                    ->once()
                ->call('assembleRequest')
                    ->once()
        ;
        
        $this->assert('test Queries\AbstractQuery::assemble when request is assembled and not forced')
            ->string($this->mock->assemble())
                ->isEqualTo('FROM `test_myTable`'."\n")
            ->mock($this->mock)
                ->call('isAssembled')
                    ->once()
                ->call('assembleRequest')
                    ->never()
        ;
        
        $this->assert('test Queries\AbstractQuery::assemble when request is assembled and forced')
            ->string($this->mock->assemble(true))
                ->isEqualTo('FROM `test_myTable`'."\n")
            ->mock($this->mock)
                ->call('isAssembled')
                    ->once()
                ->call('assembleRequest')
                    ->once()
        ;
        
        $this->assert('test Queries\AbstractQuery::assemble when request is not assembled and forced')
            ->if($setAssembledRequest->call($this->mock, ''))
            ->then
            ->string($this->mock->assemble(true))
                ->isEqualTo('FROM `test_myTable`'."\n")
            ->mock($this->mock)
                ->call('isAssembled')
                    ->once()
                ->call('assembleRequest')
                    ->once()
        ;
        
        $this->assert('test Queries\AbstractQuery::assemble when request with a diff is assembled and not forced')
            ->if($this->mock->where('test.name="atoum"'))
            ->then
            ->string($this->mock->assemble())
                ->isEqualTo('FROM `test_myTable`'."\n")
            ->mock($this->mock)
                ->call('isAssembled')
                    ->once()
                ->call('assembleRequest')
                    ->never()
        ;
        
        $this->assert('test Queries\AbstractQuery::assemble when request is assembled and forced')
            ->string($this->mock->assemble(true))
                ->isEqualTo(
                    'FROM `test_myTable`'."\n"
                    .'WHERE test.name="atoum"'."\n"
                )
            ->mock($this->mock)
                ->call('isAssembled')
                    ->once()
                ->call('assembleRequest')
                    ->once()
        ;
    }
    
    public function testExecute()
    {
        $this->assert('Queries\AbstractQuery::execute')
            ->given($setExecuter = function(\BfwSql\Executers\Common $executer) {
                $this->executer = $executer;
            })
            ->given($executer = new \mock\BfwSql\Executers\Common($this->mock))
            ->then
            ->if($setExecuter->call($this->mock, $executer))
            ->and($this->calling($executer)->execute = 42)
            ->then
            ->integer($this->mock->execute())
                ->isEqualTo(42)
            ->mock($executer)
                ->call('execute')
                    ->once()
        ;
    }
    
    public function testQuery()
    {
        $this->assert('test Queries\AbstractQuery::query')
            ->string($this->mock->getAssembledRequest())
                ->isEmpty()
            ->object($this->mock->query('SELECT id FROM test'))
                ->isIdenticalTo($this->mock)
            ->string($this->mock->getAssembledRequest())
                ->isEqualTo('SELECT id FROM test')
        ;
    }
    
    public function testAddPreparedParams()
    {
        $this->assert('test Queries\AbstractQuery::addPreparedParams')
            ->array($this->mock->getPreparedParams())
                ->isEmpty()
            ->object($this->mock->addPreparedParams([':type' => 'unit']))
                ->isIdenticalTo($this->mock)
            ->array($this->mock->getPreparedParams())
                ->isEqualTo([':type' => 'unit'])
            ->object($this->mock->addPreparedParams([':name' => 'atoum']))
                ->isIdenticalTo($this->mock)
            ->array($this->mock->getPreparedParams())
                ->isEqualTo([
                    ':type' => 'unit',
                    ':name' => 'atoum'
                ])
        ;
    }
}
