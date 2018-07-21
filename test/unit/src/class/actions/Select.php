<?php

namespace BfwSql\Actions\test\unit;

use \atoum;

$vendorPath = realpath(__DIR__.'/../../../../../vendor');
require_once($vendorPath.'/autoload.php');
require_once($vendorPath.'/bulton-fr/bfw/test/unit/helpers/Application.php');
require_once($vendorPath.'/bulton-fr/bfw/test/unit/helpers/ObserverArray.php');
require_once($vendorPath.'/bulton-fr/bfw/test/unit/mocks/src/class/Module.php');

class Select extends Atoum
{
    use \BfwSql\Test\Helpers\CreateModule;
    
    protected $mock;
    
    public function beforeTestMethod($testMethod)
    {
        $this->initModule();
        $this->createSqlConnect('myBase');
        
        $this->mockGenerator
            ->makeVisible('obtainTableInfos')
            ->makeVisible('addColumnsForSelect')
            ->makeVisible('createJoin')
            ->makeVisible('obtainPdoFetchType')
            ->makeVisible('assembleRequest')
            ->makeVisible('generateSelect')
            ->makeVisible('generateFrom')
            ->makeVisible('generateJoin')
            ->makeVisible('generateOrderBy')
            ->makeVisible('generateGroupBy')
            ->makeVisible('generateLimit')
            ->makeVisible('quoteValue')
            ->makeVisible('addQuotedColumns')
            ->makeVisible('addNotQuotedColumns')
            ->generate('BfwSql\Actions\Test\Mocks\Select')
        ;
        
        if ($testMethod === 'testConstruct') {
            return;
        }
        
        $this->mock = new \mock\BfwSql\Actions\Test\Mocks\Select($this->sqlConnect, 'object');
    }
    
    public function testConstruct()
    {
        $this->assert('test Actions\Select::__construct with object return type')
            ->object($this->mock = new \mock\BfwSql\Actions\Select($this->sqlConnect, 'object'))
                ->isInstanceOf('\BfwSql\Actions\AbstractActions')
                ->isInstanceOf('\BfwSql\Actions\Select')
            ->string($this->mock->getReturnType())
                ->isEqualTo('object')
        ;
    }
    
    public function testObtainTableInfos()
    {
        $this->assert('test Actions\Select::obtainTableInfos if not array or string')
            ->exception(function() {
                $this->mock->obtainTableInfos(42);
            })
                ->hasCode(\BfwSql\Actions\Select::ERR_TABLE_INFOS_BAD_FORMAT)
        ;
        
        $this->assert('test Actions\Select::obtainTableInfos with array')
            ->object($this->mock->obtainTableInfos(['u' => 'users']))
                ->isEqualTo((object) [
                    'tableName' => 'test_users',
                    'shortcut'  => 'u'
                ])
        ;
        
        $this->assert('test Actions\Select::obtainTableInfos with string')
            ->object($this->mock->obtainTableInfos('users'))
                ->isEqualTo((object) [
                    'tableName' => 'test_users',
                    'shortcut'  => null
                ])
        ;
    }
    
    public function testAddColumnsForSelect()
    {
        $this->assert('test Actions\Select::addColumnsForSelect with string')
            ->variable($this->mock->addColumnsForSelect('login', 'users'))
                ->isNull()
            ->array($columns = $this->mock->getColumns())
                ->isNotEmpty()
            ->object($columns[0])
                ->isEqualTo((object) [
                    'column'   => '`users`.`login`',
                    'shortcut' => null
                ])
        ;
        
        $this->assert('test Actions\Select::addColumnsForSelect with array')
            ->variable($this->mock->addColumnsForSelect(
                [ //Try many case, not search logic
                    'rDate' => 'registerDate',
                    '*',
                    'nbId' => 'COUNT(id)',
                    'DISTINCT id'
                ],
                'users'
            ))
                ->isNull()
            ->array($columns = $this->mock->getColumns())
                ->isNotEmpty()
            ->object($columns[0]) //Previous test
                ->isEqualTo((object) [
                    'column'   => '`users`.`login`',
                    'shortcut' => null
                ])
            ->object($columns[1])
                ->isEqualTo((object) [
                    'column'   => '`users`.`registerDate`',
                    'shortcut' => 'rDate'
                ])
            ->object($columns[2])
                ->isEqualTo((object) [
                    'column'   => '`users`.*',
                    'shortcut' => null
                ])
            ->object($columns[3])
                ->isEqualTo((object) [
                    'column'   => 'COUNT(id)',
                    'shortcut' => 'nbId'
                ])
            ->object($columns[4])
                ->isEqualTo((object) [
                    'column'   => 'DISTINCT id',
                    'shortcut' => null
                ])
        ;
    }
    
    public function testFrom()
    {
        $this->assert('test Actions\Select::from - prepare')
            ->if($this->calling($this->mock)->addColumnsForSelect = null)
        ;
        
        $this->assert('test Actions\Select::from without table shortcut and columns')
            ->object($this->mock->from('users'))
                ->isIdenticalTo($this->mock)
            ->object($this->mock->getMainTable())
                ->isEqualTo((object) [
                    'tableName' => 'test_users',
                    'shortcut'  => null
                ])
            ->mock($this->mock)
                ->call('addColumnsForSelect')
                    ->withArguments('*', 'test_users')
                        ->once()
        ;
        
        $this->assert('test Actions\Select::from with table shortcut and columns')
            ->object($this->mock->from(['u' => 'users'], ['id', 'login']))
                ->isIdenticalTo($this->mock)
            ->object($this->mock->getMainTable())
                ->isEqualTo((object) [
                    'tableName' => 'test_users',
                    'shortcut'  => 'u'
                ])
            ->mock($this->mock)
                ->call('addColumnsForSelect')
                    ->withArguments(['id', 'login'], 'u')
                        ->once()
        ;
    }
    
    public function testSubQuery()
    {
        $this->assert('test Actions\Select::subQuery with an incorrect object')
            ->exception(function() {
                $this->mock->subQuery((object) [], 42);
            })
                ->hasCode(\BfwSql\Actions\Select::ERR_SUB_QUERY_FORMAT)
        ;
        
        $this->assert('test Actions\Select::subQuery with a correct object')
            ->given($select = new \mock\BfwSql\Actions\Select($this->sqlConnect, 'object'))
            ->if($this->calling($select)->assemble = 'SELECT id FROM test')
            ->then
            ->object($this->mock->subQuery($select, 'testId'))
                ->isIdenticalTo($this->mock)
            ->array($subQueries = $this->mock->getSubQueries())
                ->isNotEmpty()
            ->object($subQueries[0])
                ->isEqualTo((object) [
                    'query'    => 'SELECT id FROM test',
                    'shortcut' => 'testId'
                ])
        ;
        
        $this->assert('test Actions\Select::subQuery with a string')
            ->object($this->mock->subQuery('SELECT id_user FROM users', 'userId'))
                ->isIdenticalTo($this->mock)
            ->array($subQueries = $this->mock->getSubQueries())
                ->isNotEmpty()
            ->object($subQueries[1])
                ->isEqualTo((object) [
                    'query'    => 'SELECT id_user FROM users',
                    'shortcut' => 'userId'
                ])
        ;
    }
    
    public function testCreateJoin()
    {
        $this->assert('test Actions\Select::createJoin - prepare')
            ->if($this->calling($this->mock)->addColumnsForSelect = null)
        ;
        
        $this->assert('test Actions\Select::from without shortcut')
            ->object($this->mock->createJoin('join', 'access', 'access.id_access=users.id_access', 'access.name'))
                ->isIdenticalTo($this->mock)
            ->object($this->mock->getJoin()[0])
                ->isEqualTo((object) [
                    'tableName' => 'test_access',
                    'shortcut'  => null,
                    'on'        => 'access.id_access=users.id_access'
                ])
            ->mock($this->mock)
                ->call('addColumnsForSelect')
                    ->withArguments('access.name', 'test_access')
                        ->once()
        ;
        
        $this->assert('test Actions\Select::from with shortcut')
            ->object($this->mock->createJoin('joinLeft', ['a' => 'access'], 'a.id_access=u.id_access', 'a.name'))
                ->isIdenticalTo($this->mock)
            ->object($this->mock->getJoinLeft()[0])
                ->isEqualTo((object) [
                    'tableName' => 'test_access',
                    'shortcut'  => 'a',
                    'on'        => 'a.id_access=u.id_access'
                ])
            ->mock($this->mock)
                ->call('addColumnsForSelect')
                    ->withArguments('a.name', 'a')
                        ->once()
        ;
    }
    
    public function testJoin()
    {
        $this->assert('test Actions\Select::join - prepare')
            ->if($this->calling($this->mock)->addColumnsForSelect = null)
        ;
        
        $this->assert('test Actions\Select::join with column default arg')
            ->object($this->mock->join('access', 'access.id_access=users.id_access'))
                ->isIdenticalTo($this->mock)
            ->object($this->mock->getJoin()[0])
                ->isEqualTo((object) [
                    'tableName' => 'test_access',
                    'shortcut'  => null,
                    'on'        => 'access.id_access=users.id_access'
                ])
            ->mock($this->mock)
                ->call('createJoin')
                    ->withArguments('join', 'access', 'access.id_access=users.id_access', '*')
                        ->once()
        ;
        
        $this->assert('test Actions\Select::join with column defined')
            ->object($this->mock->join(['a' => 'access'], 'a.id_access=u.id_access', 'a.name'))
                ->isIdenticalTo($this->mock)
            ->object($this->mock->getJoin()[1])
                ->isEqualTo((object) [
                    'tableName' => 'test_access',
                    'shortcut'  => 'a',
                    'on'        => 'a.id_access=u.id_access'
                ])
            ->mock($this->mock)
                ->call('createJoin')
                    ->withArguments('join', ['a' => 'access'], 'a.id_access=u.id_access', 'a.name')
                        ->once()
        ;
    }
    
    public function testJoinLeft()
    {
        $this->assert('test Actions\Select::joinLeft - prepare')
            ->if($this->calling($this->mock)->addColumnsForSelect = null)
        ;
        
        $this->assert('test Actions\Select::joinLeft with column default arg')
            ->object($this->mock->joinLeft('access', 'access.id_access=users.id_access'))
                ->isIdenticalTo($this->mock)
            ->object($this->mock->getJoinLeft()[0])
                ->isEqualTo((object) [
                    'tableName' => 'test_access',
                    'shortcut'  => null,
                    'on'        => 'access.id_access=users.id_access'
                ])
            ->mock($this->mock)
                ->call('createJoin')
                    ->withArguments('joinLeft', 'access', 'access.id_access=users.id_access', '*')
                        ->once()
        ;
        
        $this->assert('test Actions\Select::joinLeft with column defined')
            ->object($this->mock->joinLeft(['a' => 'access'], 'a.id_access=u.id_access', 'a.name'))
                ->isIdenticalTo($this->mock)
            ->object($this->mock->getJoinLeft()[1])
                ->isEqualTo((object) [
                    'tableName' => 'test_access',
                    'shortcut'  => 'a',
                    'on'        => 'a.id_access=u.id_access'
                ])
            ->mock($this->mock)
                ->call('createJoin')
                    ->withArguments('joinLeft', ['a' => 'access'], 'a.id_access=u.id_access', 'a.name')
                        ->once()
        ;
    }
    
    public function testJoinRight()
    {
        $this->assert('test Actions\Select::joinRight - prepare')
            ->if($this->calling($this->mock)->addColumnsForSelect = null)
        ;
        
        $this->assert('test Actions\Select::joinRight with column default arg')
            ->object($this->mock->joinRight('access', 'access.id_access=users.id_access'))
                ->isIdenticalTo($this->mock)
            ->object($this->mock->getJoinRight()[0])
                ->isEqualTo((object) [
                    'tableName' => 'test_access',
                    'shortcut'  => null,
                    'on'        => 'access.id_access=users.id_access'
                ])
            ->mock($this->mock)
                ->call('createJoin')
                    ->withArguments('joinRight', 'access', 'access.id_access=users.id_access', '*')
                        ->once()
        ;
        
        $this->assert('test Actions\Select::joinRight with column defined')
            ->object($this->mock->joinRight(['a' => 'access'], 'a.id_access=u.id_access', 'a.name'))
                ->isIdenticalTo($this->mock)
            ->object($this->mock->getJoinRight()[1])
                ->isEqualTo((object) [
                    'tableName' => 'test_access',
                    'shortcut'  => 'a',
                    'on'        => 'a.id_access=u.id_access'
                ])
            ->mock($this->mock)
                ->call('createJoin')
                    ->withArguments('joinRight', ['a' => 'access'], 'a.id_access=u.id_access', 'a.name')
                        ->once()
        ;
    }
    
    public function testOrder()
    {
        $this->assert('test Actions\Select::order')
            ->object($this->mock->order('users.login ASC'))
                ->isIdenticalTo($this->mock)
            ->array($this->mock->getOrder())
                ->isEqualTo([
                    'users.login ASC'
                ])
            
            ->if($this->mock->order('users.id ASC'))
            ->then
            ->array($this->mock->getOrder())
                ->isEqualTo([
                    'users.login ASC',
                    'users.id ASC'
                ])
        ;
    }
    
    public function testLimit()
    {
        $this->assert('test Actions\Select::limit with only number of items')
            ->object($this->mock->limit(5))
                ->isIdenticalTo($this->mock)
            ->string($this->mock->getLimit())
                ->isEqualTo('5')
        ;
        
        $this->assert('test Actions\Select::limit with start item and number of items')
            ->object($this->mock->limit([10, 5]))
                ->isIdenticalTo($this->mock)
            ->string($this->mock->getLimit())
                ->isEqualTo('10, 5')
        ;
    }
    
    public function testGroup()
    {
        $this->assert('test Actions\Select::group')
            ->object($this->mock->group('u.id_user'))
                ->isIdenticalTo($this->mock)
            ->array($this->mock->getGroup())
                ->isEqualTo([
                    'u.id_user'
                ])
            
            ->if($this->mock->group('u.login'))
            ->then
            ->array($this->mock->getGroup())
                ->isEqualTo([
                    'u.id_user',
                    'u.login'
                ])
        ;
    }
    
    public function testObtainPdoFetchType()
    {
        $this->assert('test Actions\Select::obtainPdoFetchType with object type')
            ->if($this->mock->setReturnType('object'))
            ->then
            ->variable($this->mock->obtainPdoFetchType())
                ->isEqualTo(\PDO::FETCH_OBJ)
        ;
        
        $this->assert('test Actions\Select::obtainPdoFetchType with array type')
            ->if($this->mock->setReturnType('array'))
            ->then
            ->variable($this->mock->obtainPdoFetchType())
                ->isEqualTo(\PDO::FETCH_ASSOC)
        ;
        
        $this->assert('test Actions\Select::obtainPdoFetchType with bad value')
            ->if($this->mock->setReturnType(42))
            ->then
            ->variable($this->mock->obtainPdoFetchType())
                ->isEqualTo(\PDO::FETCH_ASSOC)
        ;
    }
    
    public function testFetchRow()
    {
        $this->assert('test Actions\Select::fetchRow')
            ->given($pdoStatement = new \mock\PDOStatement)
            ->given($fetchReturn = (object) [
                'type'    => 'unit_test',
                'libName' => 'atoum'
            ])
            ->then
            
            ->if($this->calling($pdoStatement)->fetch = $fetchReturn)
            ->and($this->calling($this->mock)->execute = $pdoStatement)
            ->then
            
            ->object($this->mock->fetchRow())
                ->isIdenticalTo($fetchReturn)
        ;
    }
    
    public function testFetchAll()
    {
        $this->assert('test Actions\Select::fetchAll')
            ->given($pdoStatement = new \mock\PDOStatement)
            ->given($fetchReturn = [
                1 => (object) [
                    'type'    => 'unit_test',
                    'libName' => 'atoum'
                ],
                2 => (object) [
                    'type'    => 'unit_test',
                    'libName' => 'phpunit'
                ],
            ])
            ->then
            
            ->if($this->calling($pdoStatement)->fetch[1] = $fetchReturn[1])
            ->and($this->calling($pdoStatement)->fetch[2] = $fetchReturn[2])
            ->and($this->calling($pdoStatement)->fetch[3] = false)
            ->and($this->calling($this->mock)->execute = $pdoStatement)
            ->then
            
            ->generator($this->mock->fetchAll())
                ->yields->object->isIdenticalTo($fetchReturn[1])
                ->yields->object->isIdenticalTo($fetchReturn[2])
        ;
    }
    
    public function testGenerateSelect()
    {
        $this->assert('test Actions\Select::generateSelect with nothing')
            ->string($this->mock->generateSelect())
                ->isEmpty()
        ;
        
        $this->assert('test Actions\Select::generateSelect with one column')
            ->if($this->mock->addColumnsForSelect('id', 'u'))
            ->then
            ->string($this->mock->generateSelect())
                ->isEqualTo('`u`.`id`')
        ;
        
        $this->assert('test Actions\Select::generateSelect with many columns')
            ->if($this->mock->addColumnsForSelect(['login', 'pwd' => 'password'], 'u'))
            ->then
            ->string($this->mock->generateSelect())
                ->isEqualTo('`u`.`id`, `u`.`login`, `u`.`password` AS `pwd`')
        ;
        
        $this->assert('test Actions\Select::generateSelect with many columns and a sub-query')
            ->if($this->mock->subQuery('SELECT COUNT(id_sessions) FROM sessions AS s WHERE s.id_user=u.id_user', 'nbSessions'))
            ->then
            ->string($this->mock->generateSelect())
                ->isEqualTo(
                    '`u`.`id`, `u`.`login`, `u`.`password` AS `pwd`, '
                    .'(SELECT COUNT(id_sessions) FROM sessions AS s WHERE s.id_user=u.id_user) AS `nbSessions`'
                )
        ;
    }
    
    public function testGenerateFrom()
    {
        $this->assert('test Actions\Select::generateFrom with nothing')
            ->exception(function() {
                $this->mock->generateFrom();
            })
                ->hasCode(\BfwSql\Actions\Select::ERR_GENERATE_FROM_MISSING_TABLE_NAME)
        ;
        
        $this->assert('test Actions\Select::generateFrom without shortcut')
            ->if($this->mock->from('users'))
            ->then
            ->string($this->mock->generateFrom())
                ->isEqualTo('`test_users`')
        ;
        
        $this->assert('test Actions\Select::generateFrom with shortcut')
            ->if($this->mock->from(['u' => 'users']))
            ->then
            ->string($this->mock->generateFrom())
                ->isEqualTo('`test_users` AS `u`')
        ;
    }
    
    public function testGenerateJoin()
    {
        $this->assert('test Actions\Select::generateJoin with nothing')
            ->string($this->mock->generateJoin('join'))
                ->isEmpty()
            ->string($this->mock->generateJoin('joinLeft'))
                ->isEmpty()
            ->string($this->mock->generateJoin('joinRight'))
                ->isEmpty()
        ;
        
        $this->assert('test Actions\Select::generateJoin with inner join to add')
            ->if($this->mock->join('access', 'test_access.id_access=u.id_access'))
            ->then
            ->string($this->mock->generateJoin('join'))
                ->isEqualTo(' INNER JOIN `test_access` ON test_access.id_access=u.id_access')
            ->string($this->mock->generateJoin('joinLeft'))
                ->isEmpty()
            ->string($this->mock->generateJoin('joinRight'))
                ->isEmpty()
        ;
        
        $this->assert('test Actions\Select::generateJoin with left join to add')
            ->if($this->mock->joinLeft(['s' => 'sessions'], 's.id_user=u.id_user'))
            ->then
            ->string($this->mock->generateJoin('join'))
                ->isEqualTo(' INNER JOIN `test_access` ON test_access.id_access=u.id_access')
            ->string($this->mock->generateJoin('joinLeft'))
                ->isEqualTo(' LEFT JOIN `test_sessions` AS `s` ON s.id_user=u.id_user')
            ->string($this->mock->generateJoin('joinRight'))
                ->isEmpty()
        ;
        
        //I have not find good example for right join to use with others examples :/
        $this->assert('test Actions\Select::generateJoin with right join to add')
            ->if($this->mock->joinRight('b', 'b.id_user=u.id_user'))
            ->then
            ->string($this->mock->generateJoin('join'))
                ->isEqualTo(' INNER JOIN `test_access` ON test_access.id_access=u.id_access')
            ->string($this->mock->generateJoin('joinLeft'))
                ->isEqualTo(' LEFT JOIN `test_sessions` AS `s` ON s.id_user=u.id_user')
            ->string($this->mock->generateJoin('joinRight'))
                ->isEqualTo(' RIGHT JOIN `test_b` ON b.id_user=u.id_user')
        ;
    }
    
    public function testGenerateOrderBy()
    {
        $this->assert('test Actions\Select::generateOrderBy with nothing')
            ->string($this->mock->generateOrderBy())
                ->isEmpty()
        ;
        
        $this->assert('test Actions\Select::generateOrderBy with one condition')
            ->if($this->mock->order('u.id'))
            ->then
            ->string($this->mock->generateOrderBy())
                ->isEqualTo(' ORDER BY u.id')
        ;
        
        $this->assert('test Actions\Select::generateOrderBy with many conditions')
            ->if($this->mock->order('u.login'))
            ->then
            ->string($this->mock->generateOrderBy())
                ->isEqualTo(' ORDER BY u.id, u.login')
        ;
    }
    
    public function testGenerateGroupBy()
    {
        $this->assert('test Actions\Select::generateGroupBy with nothing')
            ->string($this->mock->generateGroupBy())
                ->isEmpty()
        ;
        
        $this->assert('test Actions\Select::generateGroupBy with one condition')
            ->if($this->mock->group('u.id'))
            ->then
            ->string($this->mock->generateGroupBy())
                ->isEqualTo(' GROUP BY u.id')
        ;
        
        $this->assert('test Actions\Select::generateGroupBy with many conditions')
            ->if($this->mock->group('u.login'))
            ->then
            ->string($this->mock->generateGroupBy())
                ->isEqualTo(' GROUP BY u.id, u.login')
        ;
    }
    
    public function testGenerateLimit()
    {
        $this->assert('test Actions\Select::generateLimit with nothing')
            ->string($this->mock->generateLimit())
                ->isEmpty()
        ;
        
        $this->assert('test Actions\Select::generateLimit with only number of items')
            ->if($this->mock->limit(5))
            ->then
            ->string($this->mock->generateLimit())
                ->isEqualTo(' LIMIT 5')
        ;
        
        $this->assert('test Actions\Select::generateLimit with start item and number of items')
            ->if($this->mock->limit([10, 5]))
            ->then
            ->string($this->mock->generateLimit())
                ->isEqualTo(' LIMIT 10, 5')
        ;
    }
    
    public function testAssembleRequest()
    {
        $this->assert('test Actions\Select::assembleRequest - case 1')
            ->given($this->mock->resetProperties())
            ->if($this->mock->from('users', ['id', 'login']))
            ->then
            ->variable($this->mock->assembleRequest())
                ->isNull()
            ->string($this->mock->getAssembledRequest())
                ->isEqualTo('SELECT `test_users`.`id`, `test_users`.`login` FROM `test_users`')
        ;
        
        $this->assert('test Actions\Select::assembleRequest - case 2')
            ->given($this->mock->resetProperties())
            ->if($this->mock->from('users'))
            ->and($this->mock->where('iduser=:idUser', [':idUser' => 1]))
            ->then
            ->variable($this->mock->assembleRequest())
                ->isNull()
            ->string($this->mock->getAssembledRequest())
                ->isEqualTo('SELECT `test_users`.* FROM `test_users` WHERE iduser=:idUser')
        ;
        
        $this->assert('test Actions\Select::assembleRequest - case 3')
            ->given($this->mock->resetProperties())
            ->if($this->mock->from('users'))
            ->and($this->mock->where('connectedTime > NOW()'))
            ->and($this->mock->order('iduser ASC'))
            ->and($this->mock->limit([10, 5]))
            ->then
            ->variable($this->mock->assembleRequest())
                ->isNull()
            ->string($this->mock->getAssembledRequest())
                ->isEqualTo('SELECT `test_users`.* FROM `test_users` WHERE connectedTime > NOW() ORDER BY iduser ASC LIMIT 10, 5')
        ;
        
        $this->assert('test Actions\Select::assembleRequest - case 4')
            ->given($this->mock->resetProperties())
            ->if($this->mock->from(['u' => 'users']))
            ->and($this->mock->join(['a' => 'access'], 'a.id_access=u.id_access', ['read', 'write']))
            ->and($this->mock->joinLeft(['s' => 'sessions'], 's.id_user=u.id_user'))
            ->and($this->mock->joinRight('b', 'b.id_user=u.id_user')) //always no idea for correct example
            ->and($this->mock->where('iduser=:idUser', [':idUser' => 1]))
            ->then
            ->variable($this->mock->assembleRequest())
                ->isNull()
            ->string($this->mock->getAssembledRequest())
                ->isEqualTo(
                    'SELECT `u`.*, `a`.`read`, `a`.`write`, `s`.*, `test_b`.*'
                    .' FROM `test_users` AS `u`'
                    .' INNER JOIN `test_access` AS `a` ON a.id_access=u.id_access'
                    .' LEFT JOIN `test_sessions` AS `s` ON s.id_user=u.id_user'
                    .' RIGHT JOIN `test_b` ON b.id_user=u.id_user'
                    .' WHERE iduser=:idUser'
                )
        ;
    }
    
    public function testAddQuotedColumns()
    {
        $this->assert('test Actions\Select::addQuotedColumns')
            ->exception(function() {
                $this->mock->addQuotedColumns(['lib']);
            })
                ->hasCode(\BfwSql\Actions\AbstractActions::ERR_QUOTED_COLUMN_NOT_SUPPORTED)
        ;
    }
    
    public function testAddNotQuotedColumns()
    {
        $this->assert('test Actions\Select::addNotQuotedColumns')
            ->exception(function() {
                $this->mock->addNotQuotedColumns(['dateCreate']);
            })
                ->hasCode(\BfwSql\Actions\AbstractActions::ERR_QUOTED_COLUMN_NOT_SUPPORTED)
        ;
    }
}