<?php

namespace BfwSql\test\unit;

use \atoum;

require_once(__DIR__.'/../../../../vendor/autoload.php');

class SqlUpdate extends atoum
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
        
        if (strpos($testMethod, 'testConstruct') !== false) {
            return;
        }
        
        $this->class = new \BfwSql\test\unit\mocks\SqlUpdate($this->sqlConnect, 'table_name');
    }
    
    /**
     * @return void
     */
    public function testConstructWithoutDatas()
    {
        $this->assert('test BfwSql\SqlUpdate::__construct')
            ->if($this->class = new \BfwSql\test\unit\mocks\SqlUpdate($this->sqlConnect, 'table_name'))
            ->then
            ->object($this->class->sqlConnect)
                ->isIdenticalTo($this->sqlConnect)
            ->variable($this->class->quoteStatus)
                ->isEqualTo(\BfwSql\SqlUpdate::QUOTE_ALL);
    }
    
    /**
     * @return void
     */
    public function testConstructWithDatas()
    {
        $this->assert('test BfwSql\SqlUpdate::__construct')
            ->if($this->class = new \BfwSql\test\unit\mocks\SqlUpdate(
                $this->sqlConnect,
                'table_name',
                ['title' => 'unit test']
            ))
            ->then
            ->object($this->class->sqlConnect)
                ->isIdenticalTo($this->sqlConnect)
            ->array($this->class->columns)
                ->isEqualTo(['title' => 'unit test'])
            ->variable($this->class->quoteStatus)
                ->isEqualTo(\BfwSql\SqlUpdate::QUOTE_ALL);
    }
    
    /**
     * @return void
     */
    public function testConstructForQuoteStatus()
    {
        $this->assert('test BfwSql\SqlUpdate::__construct')
            ->if($this->class = new \BfwSql\test\unit\mocks\SqlUpdate(
                $this->sqlConnect,
                'table_name',
                ['title' => 'unit test'],
                \BfwSql\SqlUpdate::QUOTE_NONE
            ))
            ->then
            ->object($this->class->sqlConnect)
                ->isIdenticalTo($this->sqlConnect)
            ->array($this->class->columns)
                ->isEqualTo(['title' => 'unit test'])
            ->variable($this->class->quoteStatus)
                ->isEqualTo(\BfwSql\SqlUpdate::QUOTE_NONE);
    }
    
    public function testAssembleRequestWithoutDatas()
    {
        $this->assert('test BfwSql\SqlUpdate::assembleRequest without datas')
            ->given($class = $this->class)
            ->exception(function() use ($class) {
                $class->callAssembleRequest();
            })
                ->hasMessage('SqlUpdate : no datas to update.');
    }
    
    public function testAssembleRequestWithDatas()
    {
        $this->assert('test BfwSql\SqlUpdate::assembleRequest with datas and default quoted status')
            ->if($this->class->addDatasForColumns(['title' => 'unit test']))
            ->then
            ->string($this->class->callAssembleRequest())
                ->isEqualTo('UPDATE unit_table_name SET `title`="unit test"');
        
        $this->assert('test BfwSql\SqlUpdate::assembleRequest with datas and partially quoted status')
            ->if($this->class->setQuoteStatus(\BfwSql\SqlUpdate::QUOTE_PARTIALLY))
            ->then
            ->string($this->class->callAssembleRequest())
                ->isEqualTo('UPDATE unit_table_name SET `title`=unit test')
            ->if($this->class->addNotQuotedColumns('title'))
            ->then
            ->string($this->class->callAssembleRequest())
                ->isEqualTo('UPDATE unit_table_name SET `title`=unit test')
            ->if($this->class->setNotQuotedColumns([]))
            ->and($this->class->addQuotedColumns('title'))
            ->then
            ->string($this->class->callAssembleRequest())
                ->isEqualTo('UPDATE unit_table_name SET `title`="unit test"')
            ->if($this->class->addDatasForColumns(['active' => 1]))
            ->then
            ->string($this->class->callAssembleRequest())
                ->isEqualTo('UPDATE unit_table_name SET `title`="unit test",`active`=1');
    }
}
