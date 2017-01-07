<?php

namespace BfwSql\test\unit;

use \atoum;

require_once(__DIR__.'/../../../../vendor/autoload.php');

class SqlSelect extends atoum
{
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
        
        $this->class = new \BfwSql\test\unit\mocks\SqlSelect($this->sqlConnect, 'array');
    }
    
    /**
     * @return void
     */
    public function testConstruct()
    {
        $this->assert('test BfwSql\SqlActions::__construct')
            ->if($this->class = new \BfwSql\test\unit\mocks\SqlSelect($this->sqlConnect, 'array'))
            ->then
            ->object($this->class->sqlConnect)
                ->isIdenticalTo($this->sqlConnect)
            ->string($this->class->returnType)
                ->isEqualTo('array')
            ->variable($this->class->mainTable)
                ->isNull()
            ->array($this->class->subQueries)
                ->isEmpty()
            ->array($this->class->join)
                ->isEmpty()
            ->array($this->class->joinLeft)
                ->isEmpty()
            ->array($this->class->joinRight)
                ->isEmpty()
            ->array($this->class->order)
                ->isEmpty()
            ->string($this->class->limit)
                ->isEmpty()
            ->array($this->class->group)
                ->isEmpty();
    }
    
    public function testObtainTableInfos()
    {
        $this->assert('test BfwSql\SqlSelect::obtainTableInfos with bad arg format')
            ->given($class = $this->class)
            ->exception(function() use ($class) {
                $class->callObtainTableInfos(true);
            })
                ->hasMessage('Table information is not in the right format.');

        $this->assert('test BfwSql\SqlSelect::obtainTableInfos with table string')
            ->object($this->class->callObtainTableInfos('table_name'))
                ->isEqualTo((object) [
                    'tableName' => 'unit_table_name',
                    'shortcut'  => null
                ]);

        $this->assert('test BfwSql\SqlSelect::obtainTableInfos with table array')
            ->object($this->class->callObtainTableInfos(['tn' => 'table_name']))
                ->isEqualTo((object) [
                    'tableName' => 'unit_table_name',
                    'shortcut'  => 'tn'
                ]);
    }
    
    public function testAddColumnsForSelect() {
        $this->assert('test BfwSql\SqlSelect::addColumnsForSelect without shortcut')
            ->if($this->class->callAddColumnsForSelect('id', 'unit_table_name'))
            ->object($this->class->columns[0])
                ->isEqualTo((object) [
                    'column'   => '`unit_table_name`.`id`',
                    'shortcut' => null
                ]);
        
        $this->assert('test BfwSql\SqlSelect::addColumnsForSelect with shortcut')
            ->if($this->class->callAddColumnsForSelect(
                ['id_table_name' => 'id'],
                'unit_table_name'
            ))
            ->object($this->class->columns[1])
                ->isEqualTo((object) [
                    'column'   => '`unit_table_name`.`id`',
                    'shortcut' => 'id_table_name'
                ]);
        
        $this->assert('test BfwSql\SqlSelect::addColumnsForSelect with sql keyword')
            ->if($this->class->callAddColumnsForSelect('DISTINCT id', 'unit_table_name'))
            ->object($this->class->columns[2])
                ->isEqualTo((object) [
                    'column'   => 'DISTINCT id',
                    'shortcut' => null
                ]);
        
        $this->assert('test BfwSql\SqlSelect::addColumnsForSelect with sql function')
            ->if($this->class->callAddColumnsForSelect('COUNT(id)', 'unit_table_name'))
            ->object($this->class->columns[3])
                ->isEqualTo((object) [
                    'column'   => 'COUNT(id)',
                    'shortcut' => null
                ]);
    }
    
    /**
     * Others cases tested by testObtainTableInfos and testAddColumnsForSelect
     * 
     * @return void
     */
    public function testFrom()
    {
        $this->assert('test BfwSql\SqlSelect::from without shortcut')
            ->object($this->class->from(['tn' => 'table_name'], 'id'))
                ->isIdenticalTo($this->class)
            ->object($this->class->mainTable)
                ->isEqualTo((object) [
                    'tableName' => 'unit_table_name',
                    'shortcut'  => 'tn'
                ])
            ->array($this->class->columns)
                ->isEqualTo([
                    (object) [
                        'column'   => '`tn`.`id`',
                        'shortcut' => null
                    ]
                ]);
    }
    
    public function testSubQuery()
    {
        $this->assert('test BfwSql\SqlSelect::subQuery with error')
            ->given($class = $this->class)
            ->exception(function() use ($class) {
                $class->subQuery(false, 'subquery');
            })
                ->hasMessage(
                    'subRequest passed in parameters must be an instance of '
                    .'BfwSql System or a string.'
                );
        
        $this->assert('test BfwSql\SqlSelect::subQuery with a SqlSelect')
            ->given($sqlSelect = new \BfwSql\test\unit\mocks\SqlSelect($this->sqlConnect, 'array'))
            ->and($sqlSelect->from('unit_test'))
            ->then
            ->object($this->class->subQuery($sqlSelect, 'subQueryObject'))
                ->isIdenticalTo($this->class)
            ->object($this->class->subQueries[0])
                ->isEqualTo((object) [
                    'query'    => $sqlSelect->assemble(),
                    'shortcut' => 'subQueryObject'
                ]);
        
        $this->assert('test BfwSql\SqlSelect::subQuery with a string')
            ->object($this->class->subQuery('SELECT id FROM test', 'subQueryString'))
                ->isIdenticalTo($this->class)
            ->object($this->class->subQueries[1])
                ->isEqualTo((object) [
                    'query'    => 'SELECT id FROM test',
                    'shortcut' => 'subQueryString'
                ]);
    }
    
    public function testJoin()
    {
        $this->assert('test BfwSql\SqlSelect::join')
            ->object($this->class->join(
                ['tj' => 'table_join'],
                'tj.joinCol=table_name.joinCol',
                'joinId'
            ))
                ->isEqualTo($this->class)
            ->array($this->class->join)
                ->isEqualTo([(object) [
                    'tableName' => 'unit_table_join',
                    'shortcut'  => 'tj',
                    'on'        => 'tj.joinCol=table_name.joinCol'
                ]])
            ->array($this->class->columns)
                ->isEqualTo([(object) [
                    'column'   => '`tj`.`joinId`',
                    'shortcut' => null
                ]]);
    }
    
    public function testJoinLeft()
    {
        $this->assert('test BfwSql\SqlSelect::joinLeft')
            ->object($this->class->joinLeft(
                ['tj' => 'table_join'],
                'tj.joinCol=table_name.joinCol',
                'joinId'
            ))
                ->isEqualTo($this->class)
            ->array($this->class->joinLeft)
                ->isEqualTo([(object) [
                    'tableName' => 'unit_table_join',
                    'shortcut'  => 'tj',
                    'on'        => 'tj.joinCol=table_name.joinCol'
                ]])
            ->array($this->class->columns)
                ->isEqualTo([(object) [
                    'column'   => '`tj`.`joinId`',
                    'shortcut' => null
                ]]);
    }
    
    public function testJoinRight()
    {
        $this->assert('test BfwSql\SqlSelect::joinRight')
            ->object($this->class->joinRight(
                ['tj' => 'table_join'],
                'tj.joinCol=table_name.joinCol',
                'joinId'
            ))
                ->isEqualTo($this->class)
            ->array($this->class->joinRight)
                ->isEqualTo([(object) [
                    'tableName' => 'unit_table_join',
                    'shortcut'  => 'tj',
                    'on'        => 'tj.joinCol=table_name.joinCol'
                ]])
            ->array($this->class->columns)
                ->isEqualTo([(object) [
                    'column'   => '`tj`.`joinId`',
                    'shortcut' => null
                ]]);
    }
    
    public function testOrder()
    {
        $this->assert('test BfwSql\SqlSelect::order')
            ->object($this->class->order('id DESC'))
                ->isIdenticalTo($this->class)
            ->array($this->class->order)
                ->isEqualTo(['id DESC'])
            ->object($this->class->order('title ASC'))
                ->isIdenticalTo($this->class)
            ->array($this->class->order)
                ->isEqualTo(['id DESC', 'title ASC']);
    }
    
    public function testLimit()
    {
        $this->assert('test BfwSql\SqlSelect::limit')
            ->object($this->class->limit(10))
                ->isIdenticalTo($this->class)
            ->string($this->class->limit)
                ->isEqualTo('10')
            ->object($this->class->limit([20, 10]))
                ->isIdenticalTo($this->class)
            ->string($this->class->limit)
                ->isEqualTo('20, 10');
    }
    
    public function testGroup()
    {
        $this->assert('test BfwSql\SqlSelect::group')
            ->object($this->class->group('id'))
                ->isIdenticalTo($this->class)
            ->array($this->class->group)
                ->isEqualTo(['id'])
            ->object($this->class->group('titre'))
                ->isIdenticalTo($this->class)
            ->array($this->class->group)
                ->isEqualTo(['id', 'titre']);
    }
    
    public function testObtainPdoFetchType()
    {
        $this->assert('test BfwSql\SqlSelect::obtainPdoFetchType for array type')
            ->if($this->class->setReturnType('array'))
            ->variable($this->class->callObtainPdoFetchType())
                ->isEqualTo(\PDO::FETCH_ASSOC);
        
        $this->assert('test BfwSql\SqlSelect::obtainPdoFetchType for object type')
            ->if($this->class->setReturnType('object'))
            ->variable($this->class->callObtainPdoFetchType())
                ->isEqualTo(\PDO::FETCH_OBJ);
    }
    
    /**
     * Need active sql connection. Tested by runner test.
     */
    public function testFetch()
    {
        
    }
    
    /**
     * Need active sql connection. Tested by runner test.
     */
    public function testFetchAll()
    {
        
    }
    
    public function testGenerateSelect()
    {
        $this->assert('test BfwSql\SqlSelect::generateSelect')
            ->if($this->class->from(['tn' => 'table_name'], ['idtn' => 'id', 'title', 'active']))
            ->and($this->class->join(['tj' => 'table_join'], 'tj.joinCol=tn.joinCol', ['idtj' => 'id']))
            ->and($this->class->subQuery('SELECT id FROM table_sub_query', 'sq'))
            ->then
            ->string($this->class->callGenerateSelect())
                ->isEqualTo(
                    '`tn`.`id` AS `idtn`, '
                    .'`tn`.`title`, '
                    .'`tn`.`active`, '
                    .'`tj`.`id` AS `idtj`, '
                    .'(SELECT id FROM table_sub_query) AS `sq`'
                );
    }
    
    public function testGenerateFrom()
    {
        $this->assert('test BfwSql\SqlSelect::generateFrom')
            ->if($this->class->from(['tn' => 'table_name']))
            ->then
            ->string($this->class->callGenerateFrom())
                ->isEqualTo('`unit_table_name` AS `tn`');
    }
    
    public function testGenerateJoin()
    {
        $this->assert('test BfwSql\SqlSelect::generateJoin')
            ->if($this->class->join(['tj' => 'table_join'], 'tj.joinCol=tn.joinCol'))
            ->and($this->class->joinLeft(['tjL' => 'table_join_left'], 'tjL.joinCol=tn.joinCol'))
            ->and($this->class->joinRight(['tjR' => 'table_join_right'], 'tjR.joinCol=tn.joinCol'))
            ->then
            ->string($this->class->callGenerateJoin('join'))
                ->isEqualTo(' INNER JOIN `unit_table_join` AS `tj` ON tj.joinCol=tn.joinCol')
            ->string($this->class->callGenerateJoin('joinLeft'))
                ->isEqualTo(' LEFT JOIN `unit_table_join_left` AS `tjL` ON tjL.joinCol=tn.joinCol')
            ->string($this->class->callGenerateJoin('joinRight'))
                ->isEqualTo(' RIGHT JOIN `unit_table_join_right` AS `tjR` ON tjR.joinCol=tn.joinCol');
    }
    
    public function testGenerateOrderBy()
    {
        $this->assert('test BfwSql\SqlSelect::generateOrderBy')
            ->if($this->class->order('id DESC'))
            ->then
            ->string($this->class->callGenerateOrderBy())
                ->isEqualTo(' ORDER BY id DESC')
            ->if($this->class->order('titre ASC'))
            ->then
            ->string($this->class->callGenerateOrderBy())
                ->isEqualTo(' ORDER BY id DESC, titre ASC');
    }
    
    public function testGenerateGroupBy()
    {
        $this->assert('test BfwSql\SqlSelect::generateGroupBy')
            ->if($this->class->group('id'))
            ->then
            ->string($this->class->callGenerateGroupBy())
                ->isEqualTo(' GROUP BY id')
            ->if($this->class->group('titre'))
            ->then
            ->string($this->class->callGenerateGroupBy())
                ->isEqualTo(' GROUP BY id, titre');
    }
    
    public function testGenerateLimit()
    {
        $this->assert('test BfwSql\SqlSelect::generateLimit')
            ->if($this->class->limit(10))
            ->then
            ->string($this->class->callGenerateLimit())
                ->isEqualTo(' LIMIT 10')
            ->if($this->class->limit([20, 10]))
            ->then
            ->string($this->class->callGenerateLimit())
                ->isEqualTo(' LIMIT 20, 10');
    }
    
    public function testAssembleRequestForMinimalRequest()
    {
        $this->assert('test BfwSql\SqlSelect::assembleRequest for minimal request')
            ->if($this->class->from(
                ['tn' => 'table_name'],
                ['idtn' => 'id', 'title', 'active']
            ))
            ->and($this->class->callAssembleRequest())
            ->then
            ->string($this->class->assembledRequest)
                ->isEqualTo(
                    'SELECT '
                    .'`tn`.`id` AS `idtn`, '
                    .'`tn`.`title`, '
                    .'`tn`.`active`'
                    .' FROM `unit_table_name` AS `tn`'
                );
    }
    
    public function testAssembleRequestForFullRequest()
    {
        $this->assert('test BfwSql\SqlSelect::assembleRequest for full request')
            ->if($this->class->from(
                ['tn' => 'table_name'],
                ['idtn' => 'id', 'title', 'active']
            ))
            ->and($this->class->join(
                ['tj' => 'table_join'],
                'tj.joinCol=tn.joinCol',
                ['idtj' => 'id'])
            )
            ->and($this->class->subQuery('SELECT id FROM table_sub_query', 'sq'))
            ->and($this->class->joinLeft(['tjL' => 'table_join_left'], 'tjL.joinCol=tn.joinCol', null))
            ->and($this->class->joinRight(['tjR' => 'table_join_right'], 'tjR.joinCol=tn.joinCol', null))
            ->and($this->class->order('id DESC'))
            ->and($this->class->order('titre ASC'))
            ->and($this->class->group('id'))
            ->and($this->class->group('titre'))
            ->and($this->class->limit([20, 10]))
            ->and($this->class->where('id=:id', [':id' => 123]))
            ->and($this->class->where('active=1'))
            ->and($this->class->callAssembleRequest())
            ->then
            ->string($this->class->assembledRequest)
                ->isEqualTo(
                    'SELECT '
                    .'`tn`.`id` AS `idtn`, '
                    .'`tn`.`title`, '
                    .'`tn`.`active`, '
                    .'`tj`.`id` AS `idtj`, '
                    .'(SELECT id FROM table_sub_query) AS `sq`'
                    .' FROM `unit_table_name` AS `tn`'
                    .' INNER JOIN `unit_table_join` AS `tj` ON tj.joinCol=tn.joinCol'
                    .' LEFT JOIN `unit_table_join_left` AS `tjL` ON tjL.joinCol=tn.joinCol'
                    .' RIGHT JOIN `unit_table_join_right` AS `tjR` ON tjR.joinCol=tn.joinCol'
                    .' WHERE id=:id AND active=1'
                    .' GROUP BY id, titre'
                    .' ORDER BY id DESC, titre ASC'
                    .' LIMIT 20, 10'
                );
    }
}
