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
 * Class de test pour SqlUpdate
 * 
 * @package bfw-sql\test
 */
class SqlUpdate extends atoum
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
        $this->class = new \BFWSql\SqlUpdate($this->sql, null, null);
        $this->mock  = new MockSqlUpdate($this->sql, null, null);
    }
    
    /**
     * Test du constructeur : SqlUpdate(Sql &$Sql, $table, $champs)
     */
    public function testSqlUpdate()
    {
        //Initialisation des attributs
        $this->string($this->mock->prefix)->isEqualTo($this->sql->prefix);
        $this->variable($this->mock->modeleName)->isNull();
        
        //Initialisation via un nom de modele et sans indiqué le nom de la table ni des champs
        $this->sql->set_modeleName('test');
        $mock = new MockSqlUpdate($this->sql, null, null);
        $this->variable($mock->table)->isIdenticalTo($mock->modeleName);
        $this->string($mock->table)->isEqualTo('test');
        
        //Initialisation via le nom de la table (sans modèle) et des données
        $mock = new MockSqlUpdate($this->sql, 'maTable', array('name' => 'monTest'));
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
        $this->mock->addChamps(array('name' => '\'monTest\'', 'desc' => '\'super Test\''));
        $this->mock->where('id=1');
        $this->mock->where('parentId=1');
        
        $this->mock->assembler_requete();
        $this->string($this->mock->RequeteAssembler)->isEqualTo('UPDATE  SET name=\'monTest\', desc=\'super Test\' WHERE id=1 AND parentId=1');
    }
    
    /**
     * Test de la méthode update($table, $champs)
     */
    public function testUpdate()
    {
        //Test du retour de la méthode ($this)
        $this->object($this->mock->update('maTable', array('name' => 'monTest')))->isInstanceOf('\BFWSql\SqlUpdate');
        
        //Test des valeurs des attributs
        $this->string($this->mock->table)->isEqualTo('maTable');
        $this->array($this->mock->champs)->isEqualTo(array('name' => 'monTest'));
    }
    
    /**
     * Test de la méthode addChamps($champs)
     */
    public function testAddChamps()
    {
        //Test du retour de la méthode($this)
        $this->object($this->mock->addChamps(array('name' => 'monTest')))->isInstanceOf('\BFWSql\SqlUpdate');
        //Test de la valeur de l'attributs
        $this->array($this->mock->champs)->isEqualTo(array('name' => 'monTest'));
        
        //Test de la levé d'exception si erreur car deux valeurs pour une même colonne.
        $mock = $this->mock;
        $this->exception(function() use($mock)
        {
           $mock->addChamps(array('name' => 'monSuperTest')); 
        })->hasMessage('Une valeur pour la colonne name est déjà déclaré.');
    }
}


/**
 * Mock pour la classe SqlUpdate
 * 
 * @package bfw-sql\test
 */
class MockSqlUpdate extends \BFWSql\SqlUpdate
{
    /**
     * Accesseur get
     */
    public function __get($name) {return $this->$name;}
}