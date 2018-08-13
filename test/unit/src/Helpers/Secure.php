<?php

namespace BfwSql\Helpers\test\unit;

use \Atoum;

$vendorPath = realpath(__DIR__.'/../../../../vendor');
require_once($vendorPath.'/autoload.php');

class Secure extends atoum
{
    use \BfwSql\Test\Helpers\CreateModule;
    
    protected $mock;
    
    public function beforeTestMethod($testMethod)
    {
        $this->initModule();
    }
    
    public function testProtectDatas()
    {
        $this->assert('Helpers\Secure::protectDatas without bases declared')
            ->exception(function() {
                \BfwSql\Helpers\Secure::protectDatas('atoum');
            })
                ->hasCode(\BfwSql\Helpers\Secure::ERR_NO_DATABASE_CONNECTED)
        ;
        
        $this->assert('Helpers\Secure::protectDatas with bases declared')
            ->given($module = $this->app->getModuleList()->getModuleByName('bfw-sql'))
            ->if($this->createSqlConnect('myBase'))
            ->and($module->listBases['myBase'] = $this->sqlConnect)
            ->then
            
            ->if($this->calling($this->pdo)->quote = function($value) {
                //I know, in reality it's not just that, but it's enough for test
                return '"'.addslashes($value).'"';
            })
            ->then
            
            ->string(\BfwSql\Helpers\Secure::protectDatas('atoum'))
                ->isEqualTo('atoum')
            
            ->string(\BfwSql\Helpers\Secure::protectDatas('test";DELETE FROM myTable;'))
                ->isEqualTo('test\";DELETE FROM myTable;')
        ;
    }
}