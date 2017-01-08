<?php

namespace BfwSql\test\unit;

use \atoum;

require_once(__DIR__.'/../../../../vendor/autoload.php');
require_once(__DIR__.'/../../helpers/Boolean.php');

class SqlActions extends atoum
{
    use \BFW\test\helpers\Boolean;
    
    /**
     * @var $class : Instance de la class
     */
    protected $class;
    
    /**
     * @var $sqlConnect Instance de la class SqlConnect envoyé au constructeur
     * d'SqlActions
     */
    protected $sqlConnect;
    
    /**
     * Instanciation de la class avant chaque méthode de test
     */
    public function beforeTestMethod($testMethod)
    {
        $connectionInfos = (object) [
            'baseKeyName' => 'unit_test',
            'host'        => 'localhost',
            'baseName'    => 'unittest',
            'user'        => 'unit',
            'password'    => 'test',
            'baseType'    => 'mysql',
            'useUTF8'     => false,
            'tablePrefix' => 'unit_'
        ];
        
        $this->sqlConnect = new \BfwSql\test\unit\mocks\SqlConnect($connectionInfos);
        
        if ($testMethod === 'testConstruct') {
            return;
        }
        
        $this->class = new \BfwSql\test\unit\mocks\SqlActions($this->sqlConnect);
    }
    
    /**
     * @return void
     */
    public function testConstruct()
    {
        $this->assert('test BfwSql\SqlActions::__construct')
            ->if($this->class = new \BfwSql\test\unit\mocks\SqlActions($this->sqlConnect))
            ->then
            ->object($this->class->sqlConnect)
                ->isIdenticalTo($this->sqlConnect);
        
        //Test properties default value
        $this->string($this->class->assembledRequest)
                ->isEmpty()
            ->boolean($this->class->isPreparedRequest)
                ->isTrue()
            ->string($this->class->tableName)
                ->isEmpty()
            ->array($this->class->columns)
                ->isEmpty()
            ->array($this->class->where)
                ->isEmpty()
            ->array($this->class->preparedRequestArgs)
                ->isEmpty()
            ->array($this->class->prepareDriversOptions)
                ->isEmpty()
            ->boolean($this->class->noResult)
                ->isFalse()
            ->variable($this->class->lastRequestStatement)
                ->isNull();
    }
    
    /**
     * @return void
     */
    public function testGetSqlConnect()
    {
        $this->assert('test BfwSql\SqlActions::getSqlConnect')
            ->object($this->class->getSqlConnect())
                ->isIdenticalTo($this->sqlConnect);
    }
    
    /**
     * @return void
     */
    public function testSetIsPreparedRequest()
    {
        $this->assert('test BfwSql\SqlActions::setIsPreparedRequest');
        $this->testSetBooleans(
            'setIsPreparedRequest',
            'isPreparedRequest',
            '\BfwSql\SqlActions'
        );
    }
    
    /**
     * @return void
     */
    public function testGetAndSetPrepareDriversOptions()
    {
        $this->assert('test BfwSql\SqlActions::setPrepareDriversOptions')
            ->object($this->class->setPrepareDriversOptions([
                \PDO::ATTR_CURSOR => \PDO::CURSOR_FWDONLY
            ]))
                ->isInstanceOf('\BfwSql\SqlActions')
            ->array($this->class->getPrepareDriversOptions())
                ->isEqualTo([
                    \PDO::ATTR_CURSOR => \PDO::CURSOR_FWDONLY
                ]);
    }
    
    /**
     * @return void
     */
    public function testIsAssembled()
    {
        $this->assert('test BfwSql\SqlActions::isAssembled')
            ->boolean($this->class->isAssembled())
                ->isFalse()
            ->then
            ->if($this->class->assembledRequest = 'test')
            ->then
            ->boolean($this->class->isAssembled())
                ->isTrue();
    }
    
    /**
     * @return void
     */
    public function testAssemble()
    {
        $this->assert('test BfwSql\SqlActions::assemble')
            ->string($this->class->assembledRequest)
                ->isEmpty()
            ->string($this->class->assemble())
                ->isEqualTo('myRequest')
            ->string($this->class->assembledRequest)
                ->isEqualTo('myRequest');
        
        $this->assert('test BfwSql\SqlActions::assemble with forced option')
            ->if($this->class->assembledRequest = 'myPersonalRequest')
            ->then
            ->string($this->class->assemble())
                ->isEqualTo('myPersonalRequest')
            ->string($this->class->assemble(true))
                ->isEqualTo('myRequest');
    }
    
    /**
     * @return void
     */
    public function testExecuteQueryPrepared()
    {
        $this->assert('test BfwSql\SqlActions::executeQuery prepared')
            ->if($this->class->callExecuteQuery())
            ->given($pdo = $this->class->getSqlConnect()->getPDO())
            ->given($sttm = $this->class->lastRequestStatement)
            ->then
            ->integer($this->sqlConnect->getNbQuery())
                ->isEqualTo(1)
            ->string($sttm->getStatement())
                ->isEqualTo('myRequest')
            ->string($pdo->getCalledMethod())
                ->isEqualTo('prepare')
            ->array($sttm->getPreparedArgs())
                ->isEmpty()
        ;
    }
    
    /**
     * Case for call PDO::query() is tested by test for SqlSelect
     * 
     * @return void
     */
    public function testExecuteQueryNotPrepared()
    {
        $this->assert('test BfwSql\SqlActions::executeQuery not prepared')
            ->if($this->class->isPreparedRequest = false)
            ->and($this->class->callExecuteQuery())
            ->given($pdo = $this->class->getSqlConnect()->getPDO())
            ->given($sttm = $this->class->lastRequestStatement)
            ->then
            ->integer($this->sqlConnect->getNbQuery())
                ->isEqualTo(1)
            ->string($sttm->getStatement())
                ->isEqualTo('myRequest')
            ->string($pdo->getCalledMethod())
                ->isEqualTo('exec')
        ;
    }
    
    /**
     * @return void
     */
    public function testExecute()
    {
        $this->class = new \BfwSql\test\unit\mocks\SqlActionsExecute($this->sqlConnect);
        
        $this->assert('test BfwSql\SqlActions::execute without error and with results')
            ->if($this->class->lastRequestStatement = new \PDOStatement())
            ->and($this->class->mockExecuteQuery = [null])
            ->and($this->class->mockRowsImpacted = 1)
            ->then
            ->object($this->class->execute())
                ->isInstanceOf('\PDOStatement')
            ->boolean($this->class->noResult)
                ->isFalse();
        
        $this->assert('test BfwSql\SqlActions::execute without error and without results')
            ->if($this->class->lastRequestStatement = new \PDOStatement())
            ->and($this->class->mockExecuteQuery = [null])
            ->and($this->class->mockRowsImpacted = 0)
            ->then
            ->object($this->class->execute())
                ->isInstanceOf('\PDOStatement')
            ->boolean($this->class->noResult)
                ->isTrue();
        
        $this->assert('test BfwSql\SqlActions::execute with error')
            ->if($this->class->lastRequestStatement = false)
            ->and($this->class->mockExecuteQuery = [null])
            ->given($class = $this->class)
            ->then
            ->exception(function() use ($class) {
                $class->execute();
            })
                ->hasMessage('An error occurred during the execution of the request');
        
        $this->assert('test BfwSql\SqlActions::execute with error')
            ->if($this->class->lastRequestStatement = new \PDOStatement())
            ->and($this->class->mockExecuteQuery = ['00001', '', 'Error msg'])
            ->given($class = $this->class)
            ->then
            ->exception(function() use ($class) {
                $class->execute();
            })
                ->hasMessage('Error msg');
    }
    
    /**
     * Sql connexion is not created. I can't test that here.
     * 
     * @return void
     */
    public function testCloseCursor()
    {
        
    }
    
    /**
     * @return void
     */
    public function testObtainImpactedRows()
    {
        $this->assert('test BfwSql\SqlActions::obtainImpactedRows for pdo::query or pdo::prepare')
            ->if($sttm = new \mock\PDOStatement)
            ->and($this->calling($sttm)->rowCount = function() {return 1;})
            ->and($this->class->lastRequestStatement = $sttm)
            ->then
            ->integer($this->class->obtainImpactedRows())
                ->isEqualTo(1);
        
        $this->assert('BfwSql::obtainImpactedRows for pdo::exec')
            ->if($this->class->lastRequestStatement = 2)
            ->then
            ->integer($this->class->obtainImpactedRows())
                ->isEqualTo(2);
        
        $this->assert('BfwSql::obtainImpactedRows with error')
            ->if($this->class->lastRequestStatement = false)
            ->then
            ->boolean($this->class->obtainImpactedRows())
                ->isFalse();
    }
    
    /**
     * @return void
     */
    public function testQuery()
    {
        $this->assert('test BfwSql\SqlActions::query')
            ->if($this->class->query('my personal query'))
            ->then
            ->string($this->class->assembledRequest)
                ->isEqualTo('my personal query');
    }
    
    /**
     * @return void
     */
    public function testWhereAndGetPreparedRequestArgs()
    {
        $this->assert('test BfwSql\SqlActions::where without prepared args')
            ->object($this->class->where('active=1'))
                ->isIdenticalTo($this->class)
            ->array($this->class->where)
                ->isEqualTo(['active=1'])
            ->array($this->class->getPreparedRequestArgs())
                ->isEqualTo([]);
        
        $this->assert('test BfwSql\SqlActions::where with prepared args')
            ->object($this->class->where('id=:id', [':id' => 123]))
                ->isIdenticalTo($this->class)
            ->array($this->class->where)
                ->isEqualTo(['active=1', 'id=:id'])
            ->array($this->class->getPreparedRequestArgs())
                ->isEqualTo([':id' => 123]);
    }
    
    /**
     * @return void
     */
    public function testAddPreparedFilters()
    {
        $this->assert('test BfwSql\SqlActions::addPreparedFilters')
            ->if($this->class->callAddPreparedFilters([':id' => 123]))
            ->then
            ->array($this->class->preparedRequestArgs)
                ->isEqualTo([':id' => 123])
            ->then
            ->if($this->class->callAddPreparedFilters([':title' => 'unit test']))
            ->then
            ->array($this->class->preparedRequestArgs)
                ->isEqualTo([
                    ':id'    => 123,
                    ':title' => 'unit test'
                ]);
    }
    
    /**
     * @return void
     */
    public function testGenerateWhere()
    {
        $this->assert('test BfwSql\SqlActions::generateWhere without filter')
            ->string($this->class->callGenerateWhere())
                ->isEmpty();
        
        $this->assert('test BfwSql\SqlActions::generateWhere with a filter')
            ->if($this->class->where = ['id=:id'])
            ->string($this->class->callGenerateWhere())
                ->isEqualTo(' WHERE id=:id');
        
        $this->assert('test BfwSql\SqlActions::generateWhere with many filter')
            ->if($this->class->where = ['id=:id', 'active=1'])
            ->string($this->class->callGenerateWhere())
                ->isEqualTo(' WHERE id=:id AND active=1');
    }
    
    /**
     * @return void
     */
    public function testAddDatasForColumns()
    {
        $this->assert('test BfwSql\SqlActions::addDatasForColums with empty array')
            ->object($this->class->addDatasForColumns([]))
                ->isIdenticalTo($this->class)
            ->array($this->class->columns)
                ->isEqualTo([]);
        
        $this->assert('test BfwSql\SqlActions::addDatasForColumns with a data')
            ->object($this->class->addDatasForColumns(['title' => 'unit test']))
                ->isIdenticalTo($this->class)
            ->array($this->class->columns)
                ->isEqualTo(['title' => 'unit test']);
        
        $this->assert('test BfwSql\SqlActions::addDatasForColumns with a other data')
            ->object($this->class->addDatasForColumns(['active' => 1]))
                ->isIdenticalTo($this->class)
            ->array($this->class->columns)
                ->isEqualTo([
                    'title'  => 'unit test',
                    'active' => 1
                ]);
        
        $this->assert('test BfwSql\SqlActions::addDatasForColumns with override data')
            ->given($class = $this->class)
            ->exception(function() use ($class) {
                $class->addDatasForColumns(['title' => 'test']);
            })
                ->hasMessage('A different data is already declared for the column title');
    }
}
