<?php
/**
 * Classes de test en rapport avec les sgdb
 * @author Vermeulen Maxime <bulton.fr@gmail.com>
 * @version 1.0
 */


namespace BFWSql\test\unit;
use \atoum;

require_once(__DIR__.'/../common.php');
 
/**
 * Class de test pour SqlConnect
 * 
 * @package bfw-sql\test
 */
class SqlConnect extends atoum
{
    /**
     * @var $class : Instance de la class SqlConnect
     */
    protected $class;
    
    /**
     * @var $class : Instance du mock pour la class SqlConnect
     */
    protected $mock;
    
    /**
     * Instanciation de la class avant chaque méthode de test
     */
    public function beforeTestMethod($testMethod)
    {
        include(__DIR__.'/../config.php');
        
        $this->class = new \BFWSql\SqlConnect($bd_host, $bd_user, $bd_pass, $bd_name, $bd_type);
        $this->mock  = new MockSqlConnect($bd_host, $bd_user, $bd_pass, $bd_name, $bd_type);
        
        $this->bd_host = $bd_host;
        $this->bd_user = $bd_user;
    }
    
    /**
     * Test du constructeur : SqlConnect($host, $login, $passe, $base, [$type='mysql', [$utf8=true]])
     */
    public function testSqlConnect()
    {
        include(__DIR__.'/../config.php');
        $mock  = new MockSqlConnect($bd_host, $bd_user, $bd_pass, $bd_name, $bd_type);
        
        //Test des attributs initilisé
        $this->boolean($mock->debug)->isFalse();
        $this->string($mock->type)->isEqualTo('mysql');
        $this->object($mock->PDO)->isInstanceOf('\PDO');
        
        //@TODO : Test du if pour la condition si utf8.
        
        //Test de la levé d'une exception si problème à la connexion
        //Utilisation de contains car le début du message diffère suivant les versions de php.
        $this->exception(function()
        {
            include(__DIR__.'/../config.php');
            new \BFWSql\SqlConnect($bd_host, $bd_user, 'Genial MotDePasse', $bd_name, $bd_type);            
        })->message->contains("[1045] Access denied for user '".$this->bd_user."'@'".$this->bd_host."' (using password: YES)");
    }
    
    /**
     * Test de la méthode set_utf8
     * Pas d'idée de comment tester ça... Le coverage le voit comme testé, étrange.
     */
    public function testSet_utf8()
    {
        
    }
    
    /**
     * Test de la méthode protect($string)
     */
    public function testProtect()
    {
        $this->string($this->class->protect("It's Ok"))->isEqualTo("It\'s Ok");
    }
    
    /**
     * Test de la méthode getPDO()
     */
    public function testGetPDO()
    {
        $this->object($this->class->getPDO())->isInstanceOf('\PDO');
    }
    
    /**
     * Test de la méthode getNbQuery()
     */
    public function testGetNbQuery()
    {
        $this->integer($this->class->getNbQuery())->isEqualTo(0);
    }
    
    /**
     * Test de la méthode upNbQuery()
     */
    public function testUpNbQuery()
    {
        $this->class->upNbQuery();
        $this->integer($this->class->getNbQuery())->isEqualTo(1);
    }
}


/**
 * Mock pour la classe SqlConnect
 * 
 * @package bfw-sql\test
 */
class MockSqlConnect extends \BFWSql\SqlConnect
{
    /**
     * Accesseur get
     */
    public function __get($name) {return $this->$name;}
}