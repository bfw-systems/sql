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
 * Class de test pour SqlAction
 * 
 * @package bfw-sql\test
 */
class SqlActions extends atoum
{
    /**
     * @var $sql : Instance de la class Sql utile à l'instanciation de la class SqlActions
     */
    protected $sql;
    
    /**
     * @var $class : Instance du mock pour la class SqlActions
     */
    protected $mock;
    
    /**
     * Instanciation de la class avant chaque méthode de test
     */
    public function beforeTestMethod($testMethod)
    {
        $this->sql = new \BFWSql\Sql();
        \BFWSql\test\setMysqlUseBufferedQuery($this->sql->getPDO());
        
        $this->mock = new MockSqlActions($this->sql);
    }
    
    /**
     * Test du constructeur : SqlActions(Sql &$Sql)
     */
    public function testSqlActions()
    {
        $mock = new MockSqlActions($this->sql);
        $this->object($mock->_sql)->isIdenticalTo($this->sql);
    }
    
    /**
     * Test de la méthode is_Assembler()
     */
    public function testIs_Assembler()
    {
        /*
         * Le mock définie la requête à "maRequete" en redifinissant la méthode 
         * assembler_requete() présente dans les classes héritant d'SqlActions
         */
        $this->mock->is_Assembler();
        $this->string($this->mock->RequeteAssembler)->isEqualTo('maRequete');
    }
    
    /**
     * Test de la méthode assemble()
     */
    public function testAssemble()
    {
        /*
         * Le mock définie la requête à "maRequete" en redifinissant la méthode 
         * assembler_requete() présente dans les classes héritant d'SqlActions
         */
        $this->string($this->mock->assemble())->isEqualTo('maRequete');
    }
    
    /**
     * Test de la méthode execute()
     */
    public function testExecute()
    {
        //Création d'une table pour tester les valeurs retournées
        $this->sql->query('
            DROP TABLE IF EXISTS SqlActions_execute;
            CREATE TABLE IF NOT EXISTS SqlActions_execute
            (
                 `id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
                 `name` VARCHAR(255),
                 PRIMARY KEY (`id`)
            ) ENGINE=MyISAM;
            
            TRUNCATE TABLE SqlActions_execute;
        ');
        
        //Test avec la requête préparé
        $this->mock->query('INSERT INTO SqlActions_execute VALUES(:id, "monTest 1");');
        $this->mock->set_prepare(array(':id' => 1));
        $this->mock->execute();
        
        //Test sans requête préparé
        $this->mock->no_prepare();
        $this->mock->query('INSERT INTO SqlActions_execute VALUES(2, "monTest 2");');
        $this->integer($this->mock->execute())->isEqualTo(1);
        
        //Test de levé d'exception si erreur
        $mock = $this->mock;
        $this->exception(function() use($mock)
        {
            $mock->query('INSERT INTO SqlActions_execute VALUES(2, "monTest 2-bis");');
            $mock->execute();
        })->hasMessage('Duplicate entry \'2\' for key \'PRIMARY\'');
    }
    
    /**
     * Test de la méthode nb_result()
     */
    public function testNb_result()
    {
        //Test de la valeur par défault
        $this->mock->req = false;
        $this->boolean($this->mock->nb_result())->isFalse();
        
        //Création d'une table et ajout de valeur pour tester
        $this->sql->query('
            DROP TABLE IF EXISTS SqlActions_nbResult;
            CREATE TABLE IF NOT EXISTS SqlActions_nbResult
            (
                 `id` INTEGER UNSIGNED NOT NULL,
                 `name` VARCHAR(255),
                 PRIMARY KEY (`id`)
            ) ENGINE=MyISAM;
            
            TRUNCATE TABLE SqlActions_nbResult;
            INSERT INTO SqlActions_nbResult VALUES(1, "monTest 1");
        ');
        
        //Test d'une requête devant retourner 1 résultat
        $req = $this->sql->query('SELECT name FROM SqlActions_nbResult WHERE id=1');
        $this->mock->req = $req;
        $this->integer($this->mock->nb_result())->isEqualTo(1);
    }
    
    /**
     * Test de la méthode query($req)
     */
    public function testQuery()
    {
        $this->mock->query('My Query');
        $this->string($this->mock->RequeteAssembler)->isEqualTo('My Query');
    }
    
    /**
     * Test de la méthode no_prepare()
     */
    public function testNo_prepare()
    {
        $this->mock->no_prepare();
        $this->boolean($this->mock->prepareBool)->isFalse();
    }
    
    /**
     * Test de la méthode set_prepare_option($option)
     */
    public function testSet_prepare_option()
    {
        $this->mock->set_prepare_option(array(':id' => 1));
        $this->array($this->mock->prepare_option)->isEqualTo(array(':id' => 1));
    }
    
    /**
     * Test de la méthode where($cond, [$prepare=null])
     */
    public function testWhere()
    {
        //Test du retour de la méthode where, correspondant à $this
        $this->object($this->mock->where('id=1'))->isIdenticalTo($this->mock);
        
        //Vérification de la valeur de l'attribut where
        $this->mock->where('id=1');
        $this->string($this->mock->where[0])->isEqualTo('id=1');
        
        //Si requête préparé, vérification de la valeur de l'attribut prepare
        $this->mock->where('id=:id', array(':id' => 1));
        $this->integer($this->mock->prepare[':id'])->isEqualTo(1);
        
        //Si erreur car la même clé de requête préparé est utilisé plusieurs fois
        $this->exception(function() {
            $sql  = new \BFWSql\Sql();
            $mock = new MockSqlActions($sql);
            
            $mock->where('id=:id', array(':id' => 1));
            $mock->where('id=:id', array(':id' => 2));
        })->hasMessage('La clé :id pour la requête sql préparé est déjà utilisé avec une autre valeur.');
    }
}


/**
 * Mock pour la classe SqlActions
 * 
 * @package bfw-sql\test
 */
class MockSqlActions extends \BFWSql\SqlActions
{
    /**
     * Accesseur get
     */
    public function __get($name) {return $this->$name;}
    
    /**
     * Assemblage des requêtes
     */
    public function assembler_requete()
    {
        $this->RequeteAssembler = 'maRequete';
    }
    
    /**
     * Accesseur set pour l'attribut prepare
     */
    public function set_prepare($value)
    {
        $this->prepare = $value;
    }
}