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
 * Class de test pour SqlDelete
 * 
 * @package bfw-sql\test
 */
class SqlDelete extends atoum
{
    /**
     * @var $sql : Instance de la class Sql utile à l'instanciation de la class SqlDelete
     */
    protected $sql;
    
    /**
     * @var $class : Instance de la class SqlDelete
     */
    protected $class;
    
    /**
     * @var $class : Instance du mock pour la class SqlDelete
     */
    protected $mock;
    
    /**
     * Instanciation de la class avant chaque méthode de test
     */
    public function beforeTestMethod($testMethod)
    {
        $this->sql   = new \BFWSql\Sql();
        $this->class = new \BFWSql\SqlDelete($this->sql, null);
        $this->mock  = new MockSqlDelete($this->sql, null);
    }
    
    /**
     * Test du constructeur : SqlDelete(Sql &$Sql)
     */
    public function testSqlDelete()
    {
        //Initialisation des attributs
        $this->string($this->mock->prefix)->isEqualTo($this->sql->getPrefix());
        $this->variable($this->mock->modeleName)->isNull();
        
        //Initialisation via un nom de modele et sans indiqué le nom de la table
        $this->sql->set_modeleName('test');
        $mock = new MockSqlDelete($this->sql, null);
        $this->variable($mock->table)->isIdenticalTo($mock->modeleName);
        $this->string($mock->table)->isEqualTo('test');
        
        //Initialisation via le nom de la table (sans modèle)
        $mock = new MockSqlDelete($this->sql, 'maTable');
        $this->string($mock->table)->isEqualTo('maTable');
    }
    
    /**
     * Test de la méthode assembler_requete()
     */
    public function testAssembler_requete()
    {
        //Test sans rien. Pas de nom de table, pas de where.
        $this->mock->assembler_requete();
        $this->string($this->mock->RequeteAssembler)->isEqualTo('DELETE FROM ');
        
        //Test avec un nom de table et des where
        $mock = new MockSqlDelete($this->sql, 'maTable');
        $mock->where('id=1');
        $mock->where('name="Test"');
        
        $mock->assembler_requete();
        $this->string($mock->RequeteAssembler)->isEqualTo('DELETE FROM maTable WHERE id=1 AND name="Test"');
    }
    
    /**
     * Test de la méthode delete($table)
     */
    public function testDelete()
    {
        $this->object($this->mock->delete('maTable'))->isInstanceOf('\BFWSql\SqlDelete');
        $this->string($this->mock->table)->isEqualTo('maTable');
    }
}


/**
 * Mock pour la classe SqlDelete
 * 
 * @package bfw-sql\test
 */
class MockSqlDelete extends \BFWSql\SqlDelete
{
    /**
     * Accesseur get
     */
    public function __get($name) {return $this->$name;}
}