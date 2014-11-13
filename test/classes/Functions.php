<?php
/**
 * Classes de test pour les fonctions
 * @author Vermeulen Maxime <bulton.fr@gmail.com>
 * @version 1.0
 */


namespace BFWSql\test\unit;
use \atoum;

require_once(__DIR__.'/../common.php');
 
/**
 * Test de la gestion des modèles
 * 
 * @package bfw-sql\test
 */
class Functions extends atoum
{
    /**
     * @var $mock : Instance de la class MockFunction pour surcharger toutes les fonctions
     */
    protected $mock;
    
    /**
     * Instanciation de la class avant chaque méthode de test
     */
    public function beforeTestMethod($testMethod)
    {
        $this->mock = new \BFWSql\Functions;
        
        include(__DIR__.'/../config.php');
        new \BFWSql\SqlConnect($bd_host, $bd_user, $bd_pass, $bd_name, $bd_type);
    }
    
    /**
     * Test du constructeur : Modeles()
     */
    public function testDB_protect()
    {
        $this->string($this->mock->DB_protect('test'))->isEqualTo('test');
        $this->string($this->mock->DB_protect('c\'est'))->isEqualTo('c\\\'est');
        
        global $DB;
        $DB = null;
        $this->string($this->mock->DB_protect('test'))->isEqualTo('test');
        $this->string($this->mock->DB_protect('c\'est'))->isEqualTo('c\'est');
    }
}

namespace BFWSql;

/**
 * Mock pour la classe abstraite Modeles
 * 
 * @package bfw-sql\test
 */
class Functions
{
    public function DB_protect($data)
    {
        return DB_protect($data);
    }
}