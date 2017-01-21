<?php

namespace BfwSql\test\unit;

use \atoum;

require_once(__DIR__.'/../../../../vendor/autoload.php');

class CreatePdoDsn extends atoum
{
    /**
     * @var \stdClass $connectionInfos
     */
    protected $connectionInfos;
    
    /**
     * @var string $unknownDriverMsg
     */
    protected $unknownDriverMsg;
    
    /**
     * Instanciation de la class avant chaque méthode de test
     */
    public function beforeTestMethod($testMethod)
    {
        $this->connectionInfos = (object) [
            'baseKeyName' => 'unit_test',
            'filePath'    => '/app/db/app.sqlite',
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
        
        $this->unknownDriverMsg = 'Sorry, the DSN drivers string is not'
            .' declared in bfw-sql module.'
            .'The main raison is the author don\'t know dsn format.'
            .'You can create an issue on github and give the correct format or'
            .', better, create a pull-request.';
    }
    
    /**
     * @return void
     */
    public function testMysql()
    {
        $this->assert('test BfwSql\CreatePdoDsn::mysql')
            ->string(\BfwSql\CreatePdoDsn::mysql($this->connectionInfos))
                ->isEqualTo('mysql:host=localhost;port=3306;dbname=unittest');
    }
    
    /**
     * @return void
     */
    public function testSqlite()
    {
        $this->assert('test BfwSql\CreatePdoDsn::sqlite')
            ->string(\BfwSql\CreatePdoDsn::sqlite($this->connectionInfos))
                ->isEqualTo('sqlite:/app/db/app.sqlite');
    }
    
    /**
     * @return void
     */
    public function testPgsql()
    {
        $this->assert('test BfwSql\CreatePdoDsn::pgsql')
            ->string(\BfwSql\CreatePdoDsn::pgsql($this->connectionInfos))
                ->isEqualTo('pgsql:host=localhost;port=3306;dbname=unittest');
    }
    
    /**
     * @return void
     */
    public function testCubrid()
    {
        $this->assert('test BfwSql\CreatePdoDsn::cubrid')
            ->string(\BfwSql\CreatePdoDsn::cubrid($this->connectionInfos))
                ->isEqualTo('cubrid:dbname=unittest;host=localhost;port=3306');
    }
    
    /**
     * @return void
     */
    public function testDblib()
    {
        $this->assert('test BfwSql\CreatePdoDsn::dblib')
            ->string(\BfwSql\CreatePdoDsn::dblib($this->connectionInfos))
                ->isEqualTo('dblib:host=localhost:3306;dbname=unittest');
    }
    
    /**
     * @return void
     */
    public function testFirebird()
    {
        $this->assert('test BfwSql\CreatePdoDsn::firebird')
            ->given($connectionInfos = $this->connectionInfos)
            ->exception(function() use ($connectionInfos) {
                \BfwSql\CreatePdoDsn::firebird($connectionInfos);
            })
                ->hasMessage($this->unknownDriverMsg);
    }
    
    /**
     * @return void
     */
    public function testIbm()
    {
        $this->assert('test BfwSql\CreatePdoDsn::ibm')
            ->given($connectionInfos = $this->connectionInfos)
            ->exception(function() use ($connectionInfos) {
                \BfwSql\CreatePdoDsn::ibm($connectionInfos);
            })
                ->hasMessage($this->unknownDriverMsg);
    }
    
    /**
     * @return void
     */
    public function testInformix()
    {
        $this->assert('test BfwSql\CreatePdoDsn::informix')
            ->given($connectionInfos = $this->connectionInfos)
            ->exception(function() use ($connectionInfos) {
                \BfwSql\CreatePdoDsn::informix($connectionInfos);
            })
                ->hasMessage($this->unknownDriverMsg);
    }
    
    /**
     * @return void
     */
    public function testSqlsrv()
    {
        $this->assert('test BfwSql\CreatePdoDsn::sqlsrv')
            ->given($connectionInfos = $this->connectionInfos)
            ->exception(function() use ($connectionInfos) {
                \BfwSql\CreatePdoDsn::sqlsrv($connectionInfos);
            })
                ->hasMessage($this->unknownDriverMsg);
    }
    
    /**
     * @return void
     */
    public function testOci()
    {
        $this->assert('test BfwSql\CreatePdoDsn::oci')
            ->given($connectionInfos = $this->connectionInfos)
            ->exception(function() use ($connectionInfos) {
                \BfwSql\CreatePdoDsn::oci($connectionInfos);
            })
                ->hasMessage($this->unknownDriverMsg);
    }
    
    /**
     * @return void
     */
    public function testOdbc()
    {
        $this->assert('test BfwSql\CreatePdoDsn::odbc')
            ->given($connectionInfos = $this->connectionInfos)
            ->exception(function() use ($connectionInfos) {
                \BfwSql\CreatePdoDsn::odbc($connectionInfos);
            })
                ->hasMessage($this->unknownDriverMsg);
    }
}
