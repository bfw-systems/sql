<?php

namespace BfwSql\test\unit;

use \atoum;

$vendorPath = realpath(__DIR__.'/../../../../vendor');
require_once($vendorPath.'/autoload.php');
require_once($vendorPath.'/bulton-fr/bfw/test/unit/helpers/ObserverArray.php');

class UsedClass extends Atoum
{
    use \BfwSql\Test\Helpers\CreateModule;
    
    protected $class;
    protected $config;
    
    public function beforeTestMethod($testMethod)
    {
        $this->initApplication();
        
        $this->config = $this->declareConfig();
        
        if ($testMethod === 'testConstructAndGetInstance') {
            return;
        }
        
        $this->class = \BfwSql\UsedClass::getInstance($this->config);
    }
    
    public function testConstructAndGetInstance()
    {
        $this->assert('test UsedClass::__construct and UsedClass::getInstance')
            ->object($usedClass = \BfwSql\UsedClass::getInstance($this->config))
                ->isInstanceOf('\BfwSql\UsedClass')
            ->object(\BfwSql\UsedClass::getInstance())
                ->isIdenticalTo($usedClass)
        ;
    }
    
    public function testGetConfig()
    {
        $this->assert('test UsedClass::getConfig')
            ->object($this->class->getConfig())
                ->isInstanceOf('\BFW\Config')
                ->isIdenticalTo($this->config)
        ;
    }
    
    public function testObtainClassNameToUse()
    {
        //Not test with non-existing key. We not test \BFW\Config here.
        
        $this->assert('test UsedClass::obtainClassNameToUse')
            ->string($this->class->obtainClassNameToUse('SqlConnect'))
                ->isEqualTo('\BfwSql\SqlConnect')
        ;
    }
}