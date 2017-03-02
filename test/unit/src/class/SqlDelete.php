<?php

namespace BfwSql\test\unit;

use \atoum;

require_once(__DIR__.'/../../../../vendor/autoload.php');

class SqlDelete extends atoum
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
        
        $this->class = new \BfwSql\test\unit\mocks\SqlDelete($this->sqlConnect, 'table_name');
    }
    
    /**
     * @return void
     */
    public function testConstruct()
    {
        $this->assert('test BfwSql\SqlDelete::__construct')
            ->if($this->class = new \BfwSql\test\unit\mocks\SqlDelete($this->sqlConnect, 'table_name'))
            ->then
            ->object($this->class->sqlConnect)
                ->isIdenticalTo($this->sqlConnect);
    }
    
    public function testAssembleRequestWithoutFilter()
    {
        $this->assert('test BfwSql\SqlDelete::assembleRequest without filter')
            ->string($this->class->callAssembleRequest())
                ->isEqualTo('DELETE FROM unit_table_name');
    }
    
    public function testAssembleRequestWithFilter()
    {
        $this->assert('test BfwSql\SqlDelete::assembleRequest with filter')
            ->if($this->class->where('id=123'))
            ->string($this->class->callAssembleRequest())
                ->isEqualTo('DELETE FROM unit_table_name WHERE id=123');
    }
    
    public function testAddQuotedColumns()
    {
        $this->assert('test BfwSql\SqlDelete::addQuotedColumns')
            ->given($class = $this->class)
            ->exception(function() use ($class) {
                $class->addNotQuotedColumns('column1');
            })
                ->hasMessage('Sorry, automatic quoted value is not supported into BfwSql\test\unit\mocks\SqlDelete class');
    }
    
    public function testAddNotQuotedColumns()
    {
        $this->assert('test BfwSql\SqlDelete::addNotQuotedColumns')
            ->given($class = $this->class)
            ->exception(function() use ($class) {
                $class->addNotQuotedColumns('column1');
            })
                ->hasMessage('Sorry, automatic quoted value is not supported into BfwSql\test\unit\mocks\SqlDelete class');
    }
}
