<?php

namespace BfwSql\test\unit;

use \atoum;

require_once(__DIR__.'/../../../../vendor/autoload.php');

class SqlInsert extends atoum
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
        
        $this->class = new \BfwSql\test\unit\mocks\SqlInsert($this->sqlConnect, 'table_name');
    }
    
    /**
     * @return void
     */
    public function testConstructWithoutDatas()
    {
        $this->assert('test BfwSql\SqlInsert::__construct')
            ->if($this->class = new \BfwSql\test\unit\mocks\SqlInsert($this->sqlConnect, 'table_name'))
            ->then
            ->object($this->class->sqlConnect)
                ->isIdenticalTo($this->sqlConnect)
            ->variable($this->class->quoteStatus)
                ->isEqualTo(\BfwSql\SqlInsert::QUOTE_ALL);
    }
    
    /**
     * @return void
     */
    public function testConstructWithDatas()
    {
        $this->assert('test BfwSql\SqlInsert::__construct')
            ->if($this->class = new \BfwSql\test\unit\mocks\SqlInsert(
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
                ->isEqualTo(\BfwSql\SqlInsert::QUOTE_ALL);
    }
    
    /**
     * @return void
     */
    public function testConstructForQuoteStatus()
    {
        $this->assert('test BfwSql\SqlInsert::__construct for quote status')
            ->if($this->class = new \BfwSql\test\unit\mocks\SqlInsert(
                $this->sqlConnect,
                'table_name',
                ['title' => 'unit test'],
                \BfwSql\SqlInsert::QUOTE_PARTIALLY
            ))
            ->then
            ->object($this->class->sqlConnect)
                ->isIdenticalTo($this->sqlConnect)
            ->array($this->class->columns)
                ->isEqualTo(['title' => 'unit test'])
            ->variable($this->class->quoteStatus)
                ->isEqualTo(\BfwSql\SqlInsert::QUOTE_PARTIALLY);
    }
    
    public function testAssembleRequestWithoutDatas()
    {
        $this->assert('test BfwSql\SqlInsert::assembleRequest without datas')
            ->string($this->class->callAssembleRequest())
                ->isEqualTo('INSERT INTO unit_table_name');
    }
    
    public function testAssembleRequestWithDatas()
    {
        $this->assert('test BfwSql\SqlInsert::assembleRequest with datas and default quoted status')
            ->if($this->class->addDatasForColumns(['title' => 'unit test']))
            ->then
            ->string($this->class->callAssembleRequest())
                ->isEqualTo('INSERT INTO unit_table_name (`title`) VALUES ("unit test")');
        
        $this->assert('test BfwSql\SqlInsert::assembleRequest with datas and partially quoted status')
            ->if($this->class->setQuoteStatus(\BfwSql\SqlInsert::QUOTE_PARTIALLY))
            ->then
            ->string($this->class->callAssembleRequest())
                ->isEqualTo('INSERT INTO unit_table_name (`title`) VALUES (unit test)')
            ->if($this->class->addNotQuotedColumns('title'))
            ->then
            ->string($this->class->callAssembleRequest())
                ->isEqualTo('INSERT INTO unit_table_name (`title`) VALUES (unit test)')
            ->if($this->class->setNotQuotedColumns([]))
            ->and($this->class->addQuotedColumns('title'))
            ->then
            ->string($this->class->callAssembleRequest())
                ->isEqualTo('INSERT INTO unit_table_name (`title`) VALUES ("unit test")')
            ->if($this->class->addDatasForColumns(['active' => 1]))
            ->then
            ->string($this->class->callAssembleRequest())
                ->isEqualTo('INSERT INTO unit_table_name (`title`,`active`) VALUES ("unit test",1)');
    }
    
    public function testFixDatasIsNull()
    {
        $this->assert('test BfwSql\SqlInsert::assembleRequest for fix when datas is null')
            ->if($this->class->addDatasForColumns([
                'title' => 'unit test',
                'desc'  => null,
                'time'  => '2017-04-01'
            ]))
            ->then
            ->string($this->class->callAssembleRequest())
                ->isEqualTo(
                    'INSERT INTO unit_table_name'
                    .' (`title`,`desc`,`time`)'
                    .' VALUES'
                    .' ("unit test",null,"2017-04-01")'
                );
    }
}
