<?php

namespace BfwSql\test\unit;

use \atoum;

require_once(__DIR__.'/../../../../vendor/autoload.php');

class SqlConnect extends atoum
{
    /**
     * @var $class : Instance de la class
     */
    protected $class;
    
    /**
     * Instanciation de la class avant chaque méthode de test
     */
    public function beforeTestMethod($testMethod)
    {
        if (strpos($testMethod, 'testConstruct') !== false) {
            return;
        }
        
        $connectionInfos          = $this->generateConnectionInfos();
        $connectionInfos->useUTF8 = false;
            
        $this->class = new \BfwSql\test\unit\mocks\SqlConnect(
            $connectionInfos
        );
    }
    
    protected function generateConnectionInfos()
    {
        return (object) [
            'baseKeyName' => 'unit_test',
            'filePath'    => '',
            'host'        => 'localhost',
            'port'        => 3306,
            'baseName'    => 'unittest',
            'user'        => 'unit',
            'password'    => 'test',
            'baseType'    => 'mysql',
            'pdoOptions'  => [],
            'useUTF8'     => true,
            'tablePrefix' => 'unit_'
        ];
    }
    
    public function testConstructWithUtf8()
    {
        $connectionInfos = $this->generateConnectionInfos();
        
        $this->assert('test BfwSql\SqlConnect::__construct with UTF-8')
            ->if($this->class = new \BfwSql\test\unit\mocks\SqlConnect($connectionInfos))
            ->then
            ->object($this->class)
                ->isInstanceOf('\BfwSql\SqlConnect')
            ->object($this->class->connectionInfos)
                ->isIdenticalTo($connectionInfos)
            ->string($this->class->type)
                ->isEqualTo($connectionInfos->baseType)
            ->given($lastStatement = $this->class->PDO->getLastStatements())
            ->string($lastStatement->getStatement())
                ->isEqualTo('SET NAMES utf8');
    }
    
    public function testConstructWithoutUtf8()
    {
        $connectionInfos = $this->generateConnectionInfos();
        
        $this->assert('test BfwSql\SqlConnect::__construct without UTF-8')
            ->if($connectionInfos->useUTF8 = false)
            ->and($this->class = new \BfwSql\test\unit\mocks\SqlConnect($connectionInfos))
            ->then
            ->object($this->class)
                ->isInstanceOf('\BfwSql\SqlConnect')
            ->object($this->class->connectionInfos)
                ->isIdenticalTo($connectionInfos)
            ->string($this->class->type)
                ->isEqualTo($connectionInfos->baseType)
            ->boolean($this->class->PDO->getLastStatements())
                ->isFalse();
    }
    
    /**
     * Tested by runner test.
     */
    public function testCreateConnection()
    {
        
    }
    
    /**
     * Test method BfwSql::protect
     * We only test if quote around string is remove
     * 
     * Test if PDO::quote is call will be run with runner test because it
     * requires a sql connection
     * 
     * @return void
     */
    public function testProtect()
    {
        $this->assert('test BfwSql\SqlConnect::protect')
            ->string($this->class->protect('unit_test'))
                ->isEqualTo('unit_test');
    }
    
    public function testGetPDO()
    {
        $this->assert('test BfwSql\SqlConnect::getPDO')
            ->object($this->class->getPDO())
                ->isInstanceOf('\PDO');
    }
    
    public function testGetConnectionInfos()
    {
        $this->assert('test BfwSql\SqlConnect::getConnectionInfos')
            ->given($connectionInfos = $this->generateConnectionInfos())
            ->and($connectionInfos->useUTF8 = false)
            ->then
            ->object($this->class->getConnectionInfos())
                ->isEqualTo($connectionInfos);
    }
    
    public function testGetType()
    {
        $this->assert('test BfwSql\SqlConnect::getType')
            ->given($connectionInfos = $this->generateConnectionInfos())
            ->string($this->class->getType())
                ->isEqualTo($connectionInfos->baseType);
    }
    
    public function testGetNbQuery()
    {
        $this->assert('test BfwSql\SqlConnect::getNbQuery')
            ->integer($this->class->getNbQuery())
                ->isEqualTo(0);
    }
    
    public function testUpNbQuery()
    {
        $this->assert('test BfwSql\SqlConnect::upNbQuery')
            ->if($this->class->upNbQuery())
            ->then
            ->integer($this->class->getNbQuery())
                ->isEqualTo(1)
            ->then
            ->if($this->class->upNbQuery())
            ->then
            ->integer($this->class->getNbQuery())
                ->isEqualTo(2);
    }
}
