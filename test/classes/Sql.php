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
 * Test de la gestion des sgdb
 * 
 * @package bfw-sql\test
 */
class Sql extends atoum
{
    /**
     * @var $class : Instance de la class Sql
     */
    protected $class;
    
    /**
     * Instanciation de la class avant chaque méthode de test
     */
    public function beforeTestMethod($testMethod)
    {
        $this->class = new \BFWSql\Sql();
        \BFWSql\test\setMysqlUseBufferedQuery($this->class->getPDO());
    }
    
    /**
     * Test du constructeur : Sql([&$DB_connect=null])
     */
    public function testSql()
    {
        //Test des attributs initialisé
        $this->string($this->class->getPrefix())->isEqualTo('');
        $this->object($this->class->getPDO())->isInstanceOf('\PDO');
        $this->object($this->class->getSqlConnect())->isInstanceOf('\BFWSql\SqlConnect');
        
        //Test de l'erreur si la classe ne trouve pas l'objet SqlConnect
        $this->exception(function()
        {
            $array = array();
            $classSql = new \BFWSql\Sql($array);
        })->hasMessage('La variable vers la connexion à la bdd doit être un objet.');
        
        
        //Test du préfix
        global $bd_prefix;
        
        $bd_prefix = 'test';
        $classSql = new \BFWSql\Sql();
        
        $this->string($classSql->getPrefix())->isEqualTo('test');
    }
    
    /**
     * Test de la méthode set_modeleName($name)
     */
    public function testSet_modeleName()
    {
        //Test de la définition d'un modèle
        $modele = $this->class->set_modeleName('monTest');
        $this->string($modele)->isEqualTo('monTest');
        $this->string($this->class->getModeleName())->isEqualTo('monTest');
        
        //Test avec prefix fait sur le test de la class Modeles
    }
    
    /**
     * Test de la méthode der_id([$name=null])
     */
    public function testDer_id()
    {
        //Création d'une table pour tester les valeurs retournées
        $this->class->query('
            DROP TABLE IF EXISTS Sql_derId;
            CREATE TABLE IF NOT EXISTS Sql_derId
            (
                 `id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
                 `name` VARCHAR(255),
                 PRIMARY KEY (`id`)
            ) ENGINE=MyISAM;
            
            TRUNCATE TABLE Sql_derId;
            INSERT INTO Sql_derId VALUES(null, "monTest 1");
        ');
        
        $this->integer($this->class->der_id())->isEqualTo(1);
        
        //Tentative avec un second enregistrement pour être sûr que c'est bien lui qui est réenvoyé
        $this->class->query('INSERT INTO Sql_derId VALUES(null, "monTest 2");');
        $this->integer($this->class->der_id())->isEqualTo(2);
    }
    
    /**
     * Test de la méthode der_id_noAI($table, $champID, $order, [$where=''])
     */
    public function testDer_id_noAI()
    {
        //Création d'une table pour tester les valeurs retournées
        $this->class->query('
            DROP TABLE IF EXISTS Sql_derIdNoAI;
            CREATE TABLE IF NOT EXISTS Sql_derIdNoAI
            (
                 `id` INTEGER UNSIGNED NOT NULL,
                 `name` VARCHAR(255),
                 `desc` VARCHAR(255),
                 PRIMARY KEY (`id`)
            ) ENGINE=MyISAM;
            
            TRUNCATE TABLE Sql_derIdNoAI;
            INSERT INTO Sql_derIdNoAI VALUES(1, "monTest 1", "test 1");
        ');
        
        //test de récupération de l'id sans et avec des informations sur le where.
        $this->integer($this->class->der_id_noAI('Sql_derIdNoAI', 'id', 'id DESC'))->isEqualTo(1);
        $this->integer(
            $this->class->der_id_noAI(
                'Sql_derIdNoAI', 
                'id', 
                array('id DESC', 'name ASC'), 
                array('name="monTest 1"', '`desc`="test 1"')
            )
        )->isEqualTo(1);
        
        //Test en insérant une autre donnée en laissant un id libre
        $this->class->query('INSERT INTO Sql_derIdNoAI VALUES(3, "monTest 2", "");');
        $this->integer($this->class->der_id_noAI('Sql_derIdNoAI', 'id', 'id DESC'))->isEqualTo(3);
        
        //Test avec une donnée qui n'existe pas dans la table
        $this->integer($this->class->der_id_noAI('Sql_derIdNoAI', 'id', 'id DESC', 'name="monTest 4"'))->isEqualTo(0);
    }
    
    /**
     * Test de la méthode select([$type='array'])
     */
    public function testSelect()
    {
        $this->object($this->class->select())->isInstanceOf('\BFWSql\SqlSelect');
        $this->integer($this->class->getNbQuery())->isEqualTo(0);
    }
    
    /**
     * Test de la méthode insert([$table=null, [$champs=null]])
     */
    public function testInsert()
    {
        $this->object($this->class->insert())->isInstanceOf('\BFWSql\SqlInsert');
        $this->integer($this->class->getNbQuery())->isEqualTo(0);
    }
    
    /**
     * Test de la méthode update([$table=null, [$champs=null]])
     */
    public function testUpdate()
    {
        $this->object($this->class->update())->isInstanceOf('\BFWSql\SqlUpdate');
        $this->integer($this->class->getNbQuery())->isEqualTo(0);
    }
    
    /**
     * Test de la méthode delete([$table=null])
     */
    public function testDelete()
    {
        $this->object($this->class->delete())->isInstanceOf('\BFWSql\SqlDelete');
        $this->integer($this->class->getNbQuery())->isEqualTo(0);
    }

    /**
     * Test de la méthode create_id($table, $champ)
     */
    public function testCreate_id()
    {
        //Création d'une table pour tester les valeurs retournées
        $this->class->query('
            DROP TABLE IF EXISTS Sql_createId;
            CREATE TABLE IF NOT EXISTS Sql_createId
            (
                 `id` INTEGER UNSIGNED NOT NULL,
                 `name` VARCHAR(255),
                 PRIMARY KEY (`id`)
            ) ENGINE=MyISAM;
            
            TRUNCATE TABLE Sql_createId;
        ');
        
        //Doit retourné 1 puisqu'aucune data dans la table
        $this->integer($this->class->create_id('Sql_createId', 'id'))->isEqualTo(1);
        
        //Insertion d'une data pour id 3, retournera 2 car recherche l'id libre juste avant le dernier trouvé.
        $this->class->query('INSERT INTO Sql_createId VALUES(3, "monTest 2");');
        $this->integer($this->class->create_id('Sql_createId', 'id'))->isEqualTo(2);
        
        //Insertion d'une data pour id 2, retournera 1
        $this->class->query('INSERT INTO Sql_createId VALUES(2, "monTest 3");');
        $this->integer($this->class->create_id('Sql_createId', 'id'))->isEqualTo(1);
        
        //Insertion d'une data pour id 1, retournera 4
        $this->class->query('INSERT INTO Sql_createId VALUES(1, "monTest 2");');
        $this->integer($this->class->create_id('Sql_createId', 'id'))->isEqualTo(4);
        
        //Test levé d'exception.
        $class = $this->class;
        $this->exception(function() use($class)
        {
            $class->create_id('Sql_createId', 'test');
        })->hasMessage("Unknown column 'Sql_createId.test' in 'field list'");
    }
    
    /**
     * Test de la méthode query($requete)
     */
    public function testQuery()
    {
        //Execute une requête et vérifie que le retour soit bien la class PDOStatement (PDO->query())
        $this->object($this->class->query('
            DROP TABLE IF EXISTS Sql_query;
            CREATE TABLE IF NOT EXISTS Sql_query
            (
                 `id` INTEGER UNSIGNED NOT NULL,
                 `name` VARCHAR(255),
                 PRIMARY KEY (`id`)
            ) ENGINE=MyISAM;
            
            TRUNCATE TABLE Sql_query;
        '))->isInstanceOf('\PDOStatement');
        
        //Test levé d'une exception car erreur dans la requête
        $class = $this->class;
        $this->exception(function() use($class)
        {
            $class->query('SELECT test FROM Sql_query;');
        })->hasMessage("Unknown column 'test' in 'field list'");
    }

    /**
     * Test de la requête upNbQuery()
     */
    public function testUpNbQuery()
    {
        $this->class->upNbQuery();
        $this->integer($this->class->getNbQuery())->isEqualTo(1);
    }
    
    /**
     * Test de la requête GetNbQuery()
     */
    public function testGetNbQuery()
    {
        $this->integer($this->class->getNbQuery())->isEqualTo(0);
        
        $this->class->upNbQuery();
        $this->integer($this->class->getNbQuery())->isEqualTo(1);
    }
}
