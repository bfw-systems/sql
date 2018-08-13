<?php

namespace BfwSql\Queries\test\unit;

use \atoum;

$vendorPath = realpath(__DIR__.'/../../../../vendor');
require_once($vendorPath.'/autoload.php');

class Select extends atoum
{
    use \BfwSql\Test\Helpers\CreateModule;
    
    protected $mock;
    
    public function beforeTestMethod($testMethod)
    {
        $this->initModule();
        $this->createSqlConnect('myBase');
        
        $this->mockGenerator
            ->makeVisible('defineQueriesParts')
            ->makeVisible('obtainGenerateOrder')
            ->makeVisible('generateSelect')
            ->makeVisible('assembleRequest')
            ->generate('BfwSql\Queries\Select')
        ;
        
        if ($testMethod === 'testConstruct') {
            return;
        }
        
        $this->mock = new \mock\BfwSql\Queries\Select($this->sqlConnect, 'object');
    }
    
    public function testConstruct()
    {
        $this->assert('test Queries\Select::__construct with object return type')
            ->object($this->mock = new \mock\BfwSql\Queries\Select($this->sqlConnect, 'object'))
                ->isInstanceOf('\BfwSql\Queries\Select')
                ->isInstanceOf('\BfwSql\Queries\AbstractQuery')
            ->string($this->mock->getExecuter()->getReturnType())
                ->isEqualTo('object')
        ;
    }
    
    public function testDefineQueriesParts()
    {
        $this->assert('test Queries\Select::defineQueriesParts')
            ->array($queriesPart = $this->mock->getQueriesParts())
                ->hasKeys([
                    'table',
                    'where',
                    'subQuery',
                    'from',
                    'join',
                    'joinLeft',
                    'joinRight',
                    'order',
                    'limit',
                    'group'
                ])
            ->object($queriesPart['table'])
                ->isInstanceOf('\BfwSql\Queries\Parts\Table')
            ->object($queriesPart['where'])
                ->isInstanceOf('\BfwSql\Queries\Parts\WhereList')
            ->object($queriesPart['subQuery'])
                ->isInstanceOf('\BfwSql\Queries\Parts\SubQueryList')
            ->object($queriesPart['from'])
                ->isInstanceOf('\BfwSql\Queries\Parts\Table')
                ->isidenticalTo($queriesPart['table'])
            ->object($queriesPart['join'])
                ->isInstanceOf('\BfwSql\Queries\Parts\JoinList')
            ->object($queriesPart['joinLeft'])
                ->isInstanceOf('\BfwSql\Queries\Parts\JoinList')
            ->object($queriesPart['joinRight'])
                ->isInstanceOf('\BfwSql\Queries\Parts\JoinList')
            ->object($queriesPart['order'])
                ->isInstanceOf('\BfwSql\Queries\Parts\OrderList')
            ->object($queriesPart['limit'])
                ->isInstanceOf('\BfwSql\Queries\Parts\Limit')
            ->object($queriesPart['group'])
                ->isInstanceOf('\BfwSql\Queries\Parts\CommonList')
            ->string($queriesPart['join']->getPartPrefix())
                ->isEqualTo('INNER JOIN')
            ->string($queriesPart['joinLeft']->getPartPrefix())
                ->isEqualTo('LEFT JOIN')
            ->string($queriesPart['joinRight']->getPartPrefix())
                ->isEqualTo('RIGHT JOIN')
            ->string($queriesPart['group']->getSeparator())
                ->isEqualTo(',')
        ;
    }
    
    public function testObtainGenerateOrder()
    {
        $this->assert('test Queries\Select::obtainGenerateOrder')
            ->array($order = $this->mock->obtainGenerateOrder())
            ->given(reset($order))
            ->string(key($order))
                ->isEqualTo('select')
                ->array($select = current($order))
                    ->hasKeys(['prefix', 'callback', 'canBeEmpty'])
                    ->string($select['prefix'])
                        ->isEqualTo('SELECT')
                    ->array($select['callback'])
                        ->isEqualTo([$this->mock, 'generateSelect'])
                    ->boolean($select['canBeEmpty'])
                        ->isFalse()
            ->given(next($order))
            ->string(key($order))
                ->isEqualTo('from')
                ->array($from = current($order))
                    ->hasKeys(['prefix', 'canBeEmpty'])
                    ->string($from['prefix'])
                        ->isEqualTo('FROM')
                    ->boolean($from['canBeEmpty'])
                        ->isFalse()
            ->given(next($order))
            ->string(key($order))
                ->isEqualTo('join')
                ->array(current($order))
                    ->isEmpty()
            ->given(next($order))
            ->string(key($order))
                ->isEqualTo('joinLeft')
                ->array(current($order))
                    ->isEmpty()
            ->given(next($order))
            ->string(key($order))
                ->isEqualTo('joinRight')
                ->array(current($order))
                    ->isEmpty()
            ->given(next($order))
            ->string(key($order))
                ->isEqualTo('where')
                ->array(current($order))
                    ->isEmpty()
            ->given(next($order))
            ->string(key($order))
                ->isEqualTo('group')
                ->array(current($order))
                    ->isEmpty()
            ->given(next($order))
            ->string(key($order))
                ->isEqualTo('order')
                ->array(current($order))
                    ->isEmpty()
            ->given(next($order))
            ->string(key($order))
                ->isEqualTo('limit')
                ->array(current($order))
                    ->isEmpty()
            ->boolean(next($order))
                ->isFalse()
        ;
    }
    
    public function testGenerateSelect()
    {
        $this->assert('test Queries\Select::generateSelect - prepare')
            ->if($this->mock->from(['u' => 'users']))
            ->then
        ;
        
        $this->assert('test Queries\Select::generateSelect with nothing')
            ->string($this->mock->generateSelect())
                ->isEmpty()
        ;
        
        $this->assert('test Queries\Select::generateSelect with one column')
            ->if($this->mock->getQueriesParts()['from']->getColumns()->__invoke(['id']))
            ->then
            ->string($this->mock->generateSelect())
                ->isEqualTo('`u`.`id`')
        ;
        
        $this->assert('test Queries\Select::generateSelect with many columns')
            ->if($this->mock->getQueriesParts()['from']->getColumns()->__invoke(['login', 'pwd' => 'password']))
            ->then
            ->string($this->mock->generateSelect())
                ->isEqualTo('`u`.`id`,`u`.`login`,`u`.`password` AS `pwd`')
        ;
        
        $this->assert('test Queries\Select::generateSelect with column on join')
            ->if($this->mock->join(['a' => 'access'], 'a.idaccess=u.idaccess', 'name'))
            ->then
            ->string($this->mock->generateSelect())
                ->isEqualTo('`u`.`id`,`u`.`login`,`u`.`password` AS `pwd`,`a`.`name`')
        ;
        
        $this->assert('test Queries\Select::generateSelect with many columns and a sub-query')
            ->if($this->mock->subQuery('nbSessions', 'SELECT COUNT(id_sessions) FROM sessions AS s WHERE s.id_user=u.id_user'))
            ->then
            ->string($this->mock->generateSelect())
                ->isEqualTo(
                    '`u`.`id`,`u`.`login`,`u`.`password` AS `pwd`,`a`.`name`,'
                    .'(SELECT COUNT(id_sessions) FROM sessions AS s WHERE s.id_user=u.id_user) AS `nbSessions`'
                )
        ;
    }
    
    public function testAssembleRequest()
    {
        $this->assert('test Queries\Select::assembleRequest - case 1')
            ->if($this->mock->from('users', ['id', 'login']))
            ->then
            ->variable($this->mock->assembleRequest())
                ->isNull()
            ->string($this->mock->getAssembledRequest())
                ->isEqualTo(
                    'SELECT `test_users`.`id`,`test_users`.`login`'."\n"
                    .'FROM `test_users`'."\n"
                )
        ;
        
        $this->assert('test Queries\Select::assembleRequest - case 2')
            ->given($this->mock = new \mock\BfwSql\Queries\Select($this->sqlConnect, 'object'))
            ->if($this->mock->from('users', '*'))
            ->and($this->mock->where('iduser=:idUser', [':idUser' => 1]))
            ->then
            ->variable($this->mock->assembleRequest())
                ->isNull()
            ->string($this->mock->getAssembledRequest())
                ->isEqualTo(
                    'SELECT `test_users`.*'."\n"
                    .'FROM `test_users`'."\n"
                    .'WHERE iduser=:idUser'."\n"
                )
        ;
        
        $this->assert('test Queries\Select::assembleRequest - case 3')
            ->given($this->mock = new \mock\BfwSql\Queries\Select($this->sqlConnect, 'object'))
            ->if($this->mock->from('users', '*'))
            ->and($this->mock->where('connectedTime > NOW()'))
            ->and($this->mock->order('iduser', 'ASC'))
            ->and($this->mock->limit(10, 5))
            ->then
            ->variable($this->mock->assembleRequest())
                ->isNull()
            ->string($this->mock->getAssembledRequest())
                ->isEqualTo(
                    'SELECT `test_users`.*'."\n"
                    .'FROM `test_users`'."\n"
                    .'WHERE connectedTime > NOW()'."\n"
                    .'ORDER BY `iduser` ASC'."\n"
                    .'LIMIT 10, 5'."\n"
                )
        ;
        
        $this->assert('test Queries\Select::assembleRequest - case 4')
            ->given($this->mock = new \mock\BfwSql\Queries\Select($this->sqlConnect, 'object'))
            ->if($this->mock->from(['u' => 'users']))
            ->and($this->mock->join(['a' => 'access'], 'a.id_access=u.id_access', ['read', 'write']))
            ->and($this->mock->join('c', 'c.id_user=u.id_user', 'id'))
            ->and($this->mock->joinLeft(['s' => 'sessions'], 's.id_user=u.id_user'))
            ->and($this->mock->joinRight('b', 'b.id_user=u.id_user')) //always no idea for correct example
            ->and($this->mock->where('iduser=:idUser', [':idUser' => 1]))
            ->then
            ->variable($this->mock->assembleRequest())
                ->isNull()
            ->string($this->mock->getAssembledRequest())
                ->isEqualTo(
                    'SELECT `a`.`read`,`a`.`write`,`test_c`.`id`,`s`.*,`test_b`.*'."\n"
                    .'FROM `test_users` AS `u`'."\n"
                    .'INNER JOIN `test_access` AS `a` ON a.id_access=u.id_access'."\n"
                    .'INNER JOIN `test_c` ON c.id_user=u.id_user'."\n"
                    .'LEFT JOIN `test_sessions` AS `s` ON s.id_user=u.id_user'."\n"
                    .'RIGHT JOIN `test_b` ON b.id_user=u.id_user'."\n"
                    .'WHERE iduser=:idUser'."\n"
                )
        ;
        
        $this->assert('test Queries\Select::assembleRequest - case 5')
            ->given($this->mock = new \mock\BfwSql\Queries\Select($this->sqlConnect, 'object'))
            ->if($this->mock->from('sales', ['year', 'profit' => 'SUM(profit)']))
            ->and($this->mock->group('year'))
            ->then
            ->variable($this->mock->assembleRequest())
                ->isNull()
            ->string($this->mock->getAssembledRequest())
                ->isEqualTo(
                    'SELECT `test_sales`.`year`,SUM(profit) AS `profit`'."\n"
                    .'FROM `test_sales`'."\n"
                    .'GROUP BY year'."\n"
                )
        ;
    }
}