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
 * Class de test pour SqlInsert
 * 
 * @package bfw-sql\test
 */
class SqlInsert extends atoum
{
    /**
     * @var $sql : Instance de la class Sql utile à l'instanciation de la class SqlInsert
     */
    protected $sql;
    
    /**
     * @var $class : Instance de la class SqlInsert
     */
    protected $class;
    
    /**
     * @var $class : Instance du mock pour la class SqlInsert
     */
    protected $mock;
    
    /**
     * Instanciation de la class avant chaque méthode de test
     */
    public function beforeTestMethod($testMethod)
    {
        $this->sql   = new \BFWSql\Sql();
        $this->class = new \BFWSql\SqlInsert($this->sql, null, null);
        $this->mock  = new MockSqlInsert($this->sql, null, null);
    }
    
    /**
     * Test du constructeur : SqlInsert(Sql &$Sql, $table, $champs)
     */
    public function testSqlInsert()
    {
        //Initialisation des attributs
        $this->string($this->mock->prefix)->isEqualTo($this->sql->getPrefix());
        $this->variable($this->mock->modeleName)->isNull();
        
        //Initialisation via un nom de modele et sans indiqué le nom de la table ni des champs
        $this->sql->set_modeleName('test');
        $mock = new MockSqlInsert($this->sql, null, null);
        $this->variable($mock->table)->isIdenticalTo($mock->modeleName);
        $this->string($mock->table)->isEqualTo('test');
        
        //Initialisation via le nom de la table (sans modèle) et des données
        $mock = new MockSqlInsert($this->sql, 'maTable', array('name' => 'monTest'));
        $this->string($mock->table)->isEqualTo('maTable');
        $this->array($mock->champs)->isEqualTo(array('name' => 'monTest'));
    }
    
    /**
     * Test de la méthode assembler_requete()
     */
    public function testAssembler_requete()
    {
        //Sans aucun paramètre, valeur d'initialisation
        $this->mock->assembler_requete();
        $this->string($this->mock->RequeteAssembler)->isEqualTo('');
        
        //Avec des paramètres
        $this->mock->data(array('id' => 1, 'name' => 'monTest'));
        $this->mock->assembler_requete();
        $this->string($this->mock->RequeteAssembler)->isEqualTo('INSERT INTO  (`id`,`name`) VALUES (\'1\',\'monTest\')');
    }
    
    /**
     * Test de la méthode insert($table, $champs)
     */
    public function testInsert()
    {
        //Test du retour de la méthode ($this)
        $this->object($this->mock->insert('maTable', array('name' => 'monTest')))->isInstanceOf('\BFWSql\SqlInsert');
        
        //Test des attributs
        $this->string($this->mock->table)->isEqualTo('maTable');
        $this->array($this->mock->champs)->isEqualTo(array('name' => 'monTest'));
    }
    
    /**
     * Test de la méthode data($champs)
     */
    public function testData()
    {
        //Test du retour de la méthode ($this)
        $this->object($this->mock->data(array('name' => 'monTest')))->isInstanceOf('\BFWSql\SqlInsert');
        
        //Test de la valeur d'attribut champs
        $this->array($this->mock->champs)->isEqualTo(array('name' => 'monTest'));
        
        //Test de la levé d'exception si erreur car deux valeurs pour une même colonne.
        $mock = $this->mock;
        $this->exception(function() use($mock)
        {
           $mock->data(array('name' => 'monSuperTest')); 
        })->hasMessage('Une valeur pour la colonne name est déjà déclaré.');
    }
}


/**
 * Mock pour la classe SqlInsert
 * 
 * @package bfw-sql\test
 */
class MockSqlInsert extends \BFWSql\SqlInsert
{
    /**
     * Accesseur get
     */
    public function __get($name) {return $this->$name;}
}