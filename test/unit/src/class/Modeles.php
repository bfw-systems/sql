<?php

namespace BfwSql\test\unit;

use \atoum;

require_once(__DIR__.'/../../../../vendor/autoload.php');

class Modeles extends atoum
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
        $this->class      = new \BfwSql\test\unit\mocks\Modeles;
        
        $this->class->tableName = 'table_name';
        $this->class->listBases = [$this->sqlConnect];
    }
    
    /**
     * @return void
     */
    public function testConstructForOneBase()
    {
        $this->assert('test BfwSql\Modeles::__construct for one base')
            ->if($this->class->callParentConstructor())
            ->then
            ->string($this->class->tableNameWithPrefix)
                ->isEqualTo('unit_table_name')
            ->object($this->class->getSqlConnect())
                ->isIdenticalTo($this->sqlConnect)
            ;
    }
    
    /**
     * @return void
     */
    public function testConstructForManyBases()
    {
        $sqlConnects = [
            'test1' => $this->sqlConnect, 
            'test2' => clone $this->sqlConnect
        ];
        
        $this->class->setListBases($sqlConnects);

        $this->assert('test BfwSql\Modeles::__construct for many bases with error')
            ->given($class = $this->class)
            ->then
            ->exception(function() use ($class) {
                $class->callParentConstructor();
            })
                ->hasMessage('There are multiple connection, so the property baseKeyName must be defined');

        $this->assert('test BfwSql\Modeles::__construct for many bases with error')
            ->given($class = $this->class)
            ->and($this->class->baseKeyName = 'test3')
            ->then
            ->exception(function() use ($class) {
                $class->callParentConstructor();
            })
                ->hasMessage('There are multiple connection, but the connection test3 is not defined.');
        
        $this->assert('test BfwSql\Modeles::__construct for many bases without error')
            ->if($this->class->baseKeyName = 'test2')
            ->and($this->class->callParentConstructor())
            ->then
            ->string($this->class->tableNameWithPrefix)
                ->isEqualTo('unit_table_name')
            ->object($this->class->getSqlConnect())
                ->isIdenticalTo($sqlConnects['test2'])
                ->isNotIdenticalTo($sqlConnects['test1'])
            ;
    }
}
