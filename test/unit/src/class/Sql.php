<?php

namespace BfwSql\test\unit;

use \atoum;

require_once(__DIR__.'/../../../../vendor/autoload.php');

class Sql extends atoum
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
        
        $this->class = new \BfwSql\Sql($this->sqlConnect);
    }
    
    /**
     * @return void
     */
    public function testConstruct()
    {
        $this->assert('test BfwSql\Sql::__construct')
            ->if($this->class = new \BfwSql\Sql($this->sqlConnect))
            ->then
            ->object($this->class->getSqlConnect())
                ->isIdenticalTo($this->sqlConnect)
            ->string($this->class->getPrefix())
                ->isEqualTo('unit_');
    }
    
    /**
     * @return void
     */
    public function testGetSqlConnect()
    {
        $this->assert('test BfwSql\Sql::getSqlConnect')
            ->object($this->class->getSqlConnect())
                ->isIdenticalTo($this->sqlConnect);
    }
    
    /**
     * @return void
     */
    public function testGetPrefix()
    {
        $this->assert('test BfwSql\Sql::getPrefix')
            ->string($this->class->getPrefix())
                ->isEqualTo('unit_');
    }
    
    /**
     * Sql connexion is not created. I can't test that here.
     * 
     * @return void
     */
    public function testGetLastInsertedId()
    {
        
    }
    
    /**
     * Sql connexion is not created. I can't test that here.
     * 
     * @return void
     */
    public function testGetLastInsertedIdWithoutAI()
    {
        
    }
    
    public function testSelect()
    {
        $this->assert('test BfwSql\Sql::select')
            ->object($this->class->select())
                ->isInstanceOf('\BfwSql\SqlSelect');
    }
    
    public function testInsert()
    {
        $this->assert('test BfwSql\Sql::insert')
            ->object($this->class->insert('table_test'))
                ->isInstanceOf('\BfwSql\SqlInsert');
    }
    
    public function testUpdate()
    {
        $this->assert('test BfwSql\Sql::update')
            ->object($this->class->update('table_test'))
                ->isInstanceOf('\BfwSql\SqlUpdate');
    }
    
    public function testDelete()
    {
        $this->assert('test BfwSql\Sql::delete')
            ->object($this->class->delete('table_test'))
                ->isInstanceOf('\BfwSql\SqlDelete');
    }
    
    /**
     * Sql connexion is not created. I can't test that here.
     * 
     * @return void
     */
    public function testCreateId()
    {
        
    }
    
    /**
     * Sql connexion is not created. I can't test that here.
     * 
     * @return void
     */
    public function testQuery()
    {
        
    }
}
