<?php

namespace BfwSql\test\unit;

use \atoum;

$vendorPath = realpath(__DIR__.'/../../../../vendor');
require_once($vendorPath.'/autoload.php');
//require_once($vendorPath.'/bulton-fr/bfw/test/unit/helpers/Application.php');

class CreatePdoDsn extends Atoum
{
    //use \BFW\Test\Helpers\Application;
    
    //protected $mock;
    
    public function beforeTestMethod($testMethod)
    {
        //$this->setRootDir(__DIR__.'/../../../..');
        //$this->createApp();
        //$this->initApp();
        
        //$this->mock = new \mock\BfwSql\CreatePdoDsn;
    }
    
    public function testMysql()
    {
        $this->assert('test CreatePdoDsn::mysql')
            ->given($infos = (object) [
                'host'     => 'localhost',
                'port'     => 3306,
                'baseName' => 'atoum'
            ])
            ->then
            ->string(\BfwSql\CreatePdoDsn::mysql($infos))
                ->isEqualTo('mysql:host=localhost;port=3306;dbname=atoum')
        ;
    }
    
    public function testSqlite()
    {
        $this->assert('test CreatePdoDsn::sqlite')
            ->given($infos = (object) [
                'filePath' => '/app/db/myapp.db'
            ])
            ->then
            ->string(\BfwSql\CreatePdoDsn::sqlite($infos))
                ->isEqualTo('sqlite:/app/db/myapp.db')
        ;
    }
    
    public function testPgsql()
    {
        $this->assert('test CreatePdoDsn::pgsql')
            ->given($infos = (object) [
                'host'     => 'localhost',
                'port'     => 3306,
                'baseName' => 'atoum'
            ])
            ->then
            ->string(\BfwSql\CreatePdoDsn::pgsql($infos))
                ->isEqualTo('pgsql:host=localhost;port=3306;dbname=atoum')
        ;
    }
    
    public function testCubrid()
    {
        $this->assert('test CreatePdoDsn::cubrid')
            ->given($infos = (object) [
                'host'     => 'localhost',
                'port'     => 3306,
                'baseName' => 'atoum'
            ])
            ->then
            ->string(\BfwSql\CreatePdoDsn::cubrid($infos))
                ->isEqualTo('cubrid:dbname=atoum;host=localhost;port=3306')
        ;
    }
    
    public function testDblib()
    {
        $this->assert('test CreatePdoDsn::dblib')
            ->given($infos = (object) [
                'host'     => 'localhost',
                'port'     => 3306,
                'baseName' => 'atoum'
            ])
            ->then
            ->string(\BfwSql\CreatePdoDsn::dblib($infos))
                ->isEqualTo('dblib:host=localhost:3306;dbname=atoum')
        ;
    }
    
    public function testFirebird()
    {
        $this->assert('test CreatePdoDsn::firebird')
            ->exception(function() {
                \BfwSql\CreatePdoDsn::firebird((object) []);
            })
                ->hasCode(\BfwSql\CreatePdoDsn::ERR_UNKNOWN_FORMAT)
        ;
    }
    
    public function testIbm()
    {
        $this->assert('test CreatePdoDsn::ibm')
            ->exception(function() {
                \BfwSql\CreatePdoDsn::ibm((object) []);
            })
                ->hasCode(\BfwSql\CreatePdoDsn::ERR_UNKNOWN_FORMAT)
        ;
    }
    
    public function testInformix()
    {
        $this->assert('test CreatePdoDsn::informix')
            ->exception(function() {
                \BfwSql\CreatePdoDsn::informix((object) []);
            })
                ->hasCode(\BfwSql\CreatePdoDsn::ERR_UNKNOWN_FORMAT)
        ;
    }
    
    public function testSqlsrv()
    {
        $this->assert('test CreatePdoDsn::sqlsrv')
            ->exception(function() {
                \BfwSql\CreatePdoDsn::sqlsrv((object) []);
            })
                ->hasCode(\BfwSql\CreatePdoDsn::ERR_UNKNOWN_FORMAT)
        ;
    }
    
    public function testOci()
    {
        $this->assert('test CreatePdoDsn::oci')
            ->exception(function() {
                \BfwSql\CreatePdoDsn::oci((object) []);
            })
                ->hasCode(\BfwSql\CreatePdoDsn::ERR_UNKNOWN_FORMAT)
        ;
    }
    
    public function testOdbc()
    {
        $this->assert('test CreatePdoDsn::odbc')
            ->exception(function() {
                \BfwSql\CreatePdoDsn::odbc((object) []);
            })
                ->hasCode(\BfwSql\CreatePdoDsn::ERR_UNKNOWN_FORMAT)
        ;
    }
}