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
 * Class de test pour SqlSelect
 * 
 * @package bfw-sql\test
 */
class SqlSelect extends atoum
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
        $this->class = new \BFWSql\SqlSelect($this->sql, 'array');
        $this->mock  = new MockSqlSelect($this->sql, 'array');
    }
    
    /**
     * Test du constructeur : SqlSelect(Sql &$Sql, $type)
     */
    public function testSqlSelect()
    {
        $mock = new MockSqlSelect($this->sql, 'array');
        $this->string($mock->prefix)->isEqualTo($this->sql->prefix);
        $this->variable($mock->modeleName)->isNull();
        $this->string($mock->typeResult)->isEqualTo('array');
    }
    
    /**
     * Test de la méthode get_no_result()
     */
    public function testGet_no_result()
    {
        $this->mock->get_no_result();
        $this->boolean($this->mock->no_result)->isFalse(); //Valeur d'initialisation
    }
    
    /**
     * Test de la méthode assembler_requete()
     */
    public function testAssembler_requete()
    {
        //Test select + from
        $this->mock->from(array('mt' => 'maTable'), '*');
        $this->mock->assembler_requete();
        $this->string($this->mock->RequeteAssembler)->isEqualTo('SELECT `mt`.* FROM `maTable` AS `mt`');
        
        $this->mock->select = array(); //Reinit liste des champs
        $this->mock->from(array('mt' => 'maTable'), array('id' => 'idTable', 'name' => 'name'));
        $this->mock->assembler_requete();
        $this->string($this->mock->RequeteAssembler)->isEqualTo('SELECT `mt`.`idTable` AS id, `mt`.`name` AS name FROM `maTable` AS `mt`');
        
        //Test du select avec uniquement le nom du modèle.
        $mock = new MockSqlSelect($this->sql, 'array');
        $mock->modeleName = 'maTable';
        $mock->assembler_requete();
        $this->string($mock->RequeteAssembler)->isEqualTo('SELECT * FROM `maTable`');
        
        //Test sous requête
        $mock = new MockSqlSelect($this->sql, 'array');
        $mock->from(array('mt' => 'maTable'), array('id' => 'idTable'));
        
        $sqlAction = new \BFWSql\SqlActions($this->sql);
        $query = 'SELECT name FROM SqlSelect_subQuery WHERE id=1';
        $sqlAction->query($query);
        $mock->subQuery($sqlAction, 'myQuery');
        
        $mock->assembler_requete();
        $this->string($mock->RequeteAssembler)->isEqualTo('SELECT `mt`.`idTable` AS id, (SELECT name FROM SqlSelect_subQuery WHERE id=1) AS `myQuery` FROM `maTable` AS `mt`');
        
        //Test du from sans alias définie pour la table
        $mock = new MockSqlSelect($this->sql, 'array');
        $mock->from('maTable', array('id' => 'idTable'));
        $mock->assembler_requete();
        $this->string($mock->RequeteAssembler)->isEqualTo('SELECT `maTable`.`idTable` AS id FROM `maTable`');
        
        //Test inner join
        $mock = new MockSqlSelect($this->sql, 'array');
        $mock->from(array('mt' => 'maTable'), array('id' => 'idTable'));
        $mock->join(array('jt' => 'joinTable'), 'jt.id=mt.id', '');
        $mock->join(array('jt2' => 'joinTable2'), 'jt2.id=mt.id', '');
        $mock->assembler_requete();
        $this->string($mock->RequeteAssembler)->isEqualTo('SELECT `mt`.`idTable` AS id FROM `maTable` AS `mt` INNER JOIN `joinTable` AS `jt` ON jt.id=mt.id INNER JOIN `joinTable2` AS `jt2` ON jt2.id=mt.id');
        
        $mock = new MockSqlSelect($this->sql, 'array');
        $mock->from(array('mt' => 'maTable'), array('id' => 'idTable'));
        $mock->join('joinTable', 'joinTable.id=mt.id');
        $mock->assembler_requete();
        $this->string($mock->RequeteAssembler)->isEqualTo('SELECT `mt`.`idTable` AS id, `joinTable`.* FROM `maTable` AS `mt` INNER JOIN `joinTable` ON joinTable.id=mt.id');
        
        //Test left join
        $mock = new MockSqlSelect($this->sql, 'array');
        $mock->from(array('mt' => 'maTable'), array('id' => 'idTable'));
        $mock->joinLeft(array('jt' => 'joinTable'), 'jt.id=mt.id', '');
        $mock->joinLeft(array('jt2' => 'joinTable2'), 'jt2.id=mt.id', '');
        $mock->assembler_requete();
        $this->string($mock->RequeteAssembler)->isEqualTo('SELECT `mt`.`idTable` AS id FROM `maTable` AS `mt` LEFT JOIN `joinTable` AS `jt` ON jt.id=mt.id LEFT JOIN `joinTable2` AS `jt2` ON jt2.id=mt.id');
        
        $mock = new MockSqlSelect($this->sql, 'array');
        $mock->from(array('mt' => 'maTable'), array('id' => 'idTable'));
        $mock->joinLeft('joinTable', 'joinTable.id=mt.id');
        $mock->assembler_requete();
        $this->string($mock->RequeteAssembler)->isEqualTo('SELECT `mt`.`idTable` AS id, `joinTable`.* FROM `maTable` AS `mt` LEFT JOIN `joinTable` ON joinTable.id=mt.id');
        
        //Test right join
        $mock = new MockSqlSelect($this->sql, 'array');
        $mock->from(array('mt' => 'maTable'), array('id' => 'idTable'));
        $mock->joinRight(array('jt' => 'joinTable'), 'jt.id=mt.id', '');
        $mock->joinRight(array('jt2' => 'joinTable2'), 'jt2.id=mt.id', '');
        $mock->assembler_requete();
        $this->string($mock->RequeteAssembler)->isEqualTo('SELECT `mt`.`idTable` AS id FROM `maTable` AS `mt` RIGHT JOIN `joinTable` AS `jt` ON jt.id=mt.id RIGHT JOIN `joinTable2` AS `jt2` ON jt2.id=mt.id');
        
        $mock = new MockSqlSelect($this->sql, 'array');
        $mock->from(array('mt' => 'maTable'), array('id' => 'idTable'));
        $mock->joinRight('joinTable', 'joinTable.id=mt.id');
        $mock->assembler_requete();
        $this->string($mock->RequeteAssembler)->isEqualTo('SELECT `mt`.`idTable` AS id, `joinTable`.* FROM `maTable` AS `mt` RIGHT JOIN `joinTable` ON joinTable.id=mt.id');
        
        
        //Test where
        $mock = new MockSqlSelect($this->sql, 'array');
        $mock->from(array('mt' => 'maTable'), 'name');
        $mock->where('id=:id', array(':id' => 1));
        $mock->where('idParent=:idP', array(':idP' => 1));
        $mock->assembler_requete();
        $this->string($mock->RequeteAssembler)->isEqualTo('SELECT `mt`.`name` FROM `maTable` AS `mt` WHERE id=:id AND idParent=:idP');
        
        
        //Test order
        $mock = new MockSqlSelect($this->sql, 'array');
        $mock->from(array('mt' => 'maTable'), 'name');
        $mock->order('id ASC');
        $mock->order('idParent ASC');
        $mock->assembler_requete();
        $this->string($mock->RequeteAssembler)->isEqualTo('SELECT `mt`.`name` FROM `maTable` AS `mt` ORDER BY id ASC, idParent ASC');
        
        
        //Test group by
        $mock = new MockSqlSelect($this->sql, 'array');
        $mock->from(array('mt' => 'maTable'), 'name');
        $mock->group('id');
        $mock->group('idParent');
        $mock->assembler_requete();
        $this->string($mock->RequeteAssembler)->isEqualTo('SELECT `mt`.`name` FROM `maTable` AS `mt` GROUP BY id, idParent');
        
        
        //Test limit
        $mock = new MockSqlSelect($this->sql, 'array');
        $mock->from(array('mt' => 'maTable'), 'name');
        $mock->limit(1);
        $mock->assembler_requete();
        $this->string($mock->RequeteAssembler)->isEqualTo('SELECT `mt`.`name` FROM `maTable` AS `mt` LIMIT 1');
        
    }
    
    /**
     * Test de la méthode infosTable($table)
     */
    public function testInfosTable()
    {
        $this->array($this->mock->infosTable('maTable'))->isEqualTo(array('tableName' => 'maTable', 'as' => 'maTable'));
        $this->array($this->mock->infosTable(array('mt' => 'maTable')))->isEqualTo(array('tableName' => 'maTable', 'as' => 'mt'));
    }
    
    /**
     * Test de la méthode addChamps($champs, &$array, $as)
     */
    public function testAddChamps()
    {
        //Test avec un alias
        $champs = array();
        
        $this->mock->addChamps(array('id' => 'idTable'), $champs, 'maTable');
        $this->array($champs[0])->isEqualTo(array('`maTable`.`idTable`', 'id'));
        
        $this->mock->addChamps(array(0 => 'idTable'), $champs, 'maTable');
        $this->array($champs[1])->isEqualTo(array('`maTable`.`idTable`'));
        
        $this->mock->addChamps(array('nbId' => 'COUNT(id)'), $champs, 'maTable');
        $this->array($champs[2])->isEqualTo(array('COUNT(id)', 'nbId'));
        
        $this->mock->addChamps(array(0 => 'COUNT(id)'), $champs, 'maTable');
        $this->array($champs[3])->isEqualTo(array('COUNT(id)'));
        
        
        //Test sans alias
        $champs = array();
        
        $this->mock->addChamps('*', $champs, 'maTable');
        $this->array($champs[0])->isEqualTo(array('`maTable`.*'));
        
        $this->mock->addChamps('id', $champs, 'maTable');
        $this->array($champs[1])->isEqualTo(array('`maTable`.`id`'));
        
        $this->mock->addChamps('COUNT(id)', $champs, 'maTable');
        $this->array($champs[2])->isEqualTo(array('COUNT(id)'));
    }
    
    /**
     * Test de la méthode from($table, [$champs='*'])
     */
    public function testFrom()
    {
        $this->object($this->mock->from('maTable'))->isInstanceOf('\BFWSql\SqlSelect');
        $this->array($this->mock->from)->isEqualTo(array('tableName' => 'maTable', 'as' => 'maTable'));
        
        $this->mock->from(array('mt' => 'maTable'));
        $this->array($this->mock->from)->isEqualTo(array('tableName' => 'maTable', 'as' => 'mt'));
    }
    
    /**
     * Test de la méthode subQuery($req, $as)
     */
    public function testSubQuery()
    {
        //Création de l'objet SqlActions pour la sous-requête (normalement SqlSelect)
        $sqlAction = new \BFWSql\SqlActions($this->sql);
        $query = 'SELECT name FROM SqlSelect_subQuery WHERE id=1';
        $sqlAction->query($query);
        
        //Test du retour de la méthode ($this)
        $this->object($this->mock->subQuery($sqlAction, 'myQuery'))->isInstanceOf('\BFWSql\SqlSelect');
        
        //Test de la valeur de l'attribut subQuery
        $this->array($this->mock->subQuery[0])->isEqualTo(array('('.$query.')', 'myQuery'));
    }
    
    /**
     * Test de la méthode join($table, $on, [$champs='*'])
     */
    public function testJoin()
    {
        $this->object($this->mock->join('join', 'matable.id=join.id'))->isInstanceOf('\BFWSql\SqlSelect');
        $this->array($this->mock->join[0])->isEqualTo(array('tableName' => 'join', 'as' => 'join', 'on' => 'matable.id=join.id'));
    }
    
    /**
     * Test de la méthode joinLeft($table, $on, [$champs='*'])
     */
    public function testJoinLeft()
    {
        $this->object($this->mock->joinLeft('join', 'matable.id=join.id'))->isInstanceOf('\BFWSql\SqlSelect');
        $this->array($this->mock->joinLeft[0])->isEqualTo(array('tableName' => 'join', 'as' => 'join', 'on' => 'matable.id=join.id'));
    }
    
    /**
     * Test de la méthode joinRight($table, $on, [$champs='*'])
     */
    public function testJoinRight()
    {
        $this->object($this->mock->joinRight('join', 'matable.id=join.id'))->isInstanceOf('\BFWSql\SqlSelect');
        $this->array($this->mock->joinRight[0])->isEqualTo(array('tableName' => 'join', 'as' => 'join', 'on' => 'matable.id=join.id'));
    }
    
    /**
     * Test de la méthode order($cond)
     */
    public function testOrder()
    {
        $this->object($this->mock->order('id ASC'))->isInstanceOf('\BFWSql\SqlSelect');
        $this->string($this->mock->order[0])->isEqualTo('id ASC');
    }
    
    /**
     * Test de la méthode limit($limit)
     */
    public function testLimit()
    {
        //Test du retour de la méthode ($this) et de la valeur de l'attribut limit
        $this->object($this->mock->limit('1'))->isInstanceOf('\BFWSql\SqlSelect');
        $this->string($this->mock->limit)->isEqualTo('1');
        
        //Test avec l'attribut au format array
        $this->mock->limit(array(1));
        $this->integer($this->mock->limit)->isEqualTo(1);
        
        //Test de l'attribut avec un array à 2 valeurs
        $this->mock->limit(array(0, 10));
        $this->string($this->mock->limit)->isEqualTo('0, 10');
    }
    
    /**
     * Test de la méthode group($cond)
     */
    public function testGroup()
    {
        $this->object($this->mock->group('id'))->isInstanceOf('\BFWSql\SqlSelect');
        $this->string($this->mock->group[0])->isEqualTo('id');
    }
    
    /**
     * Test de la méthode executeReq()
     */
    public function testExecuteReq()
    {
        //Création d'une table et ajout de valeur pour tester
        $this->sql->query('
            DROP TABLE IF EXISTS SqlSelect_executeReq;
            CREATE TABLE IF NOT EXISTS SqlSelect_executeReq
            (
                 `id` INTEGER UNSIGNED NOT NULL,
                 `name` VARCHAR(255),
                 PRIMARY KEY (`id`)
            ) ENGINE=MyISAM;
            
            TRUNCATE TABLE SqlSelect_executeReq;
            INSERT INTO SqlSelect_executeReq VALUES(1, "monTest 1");
        ');
        
        //Test requête préparé
        $mock = new MockSqlSelect($this->sql, 'array');
        $mock->from('SqlSelect_executeReq', 'name')->where('id=:id', array(':id' => 1));
        $this->object($mock->executeReq())->isInstanceOf('\PDOStatement');
        
        //Test requête non préparé
        $mock = new MockSqlSelect($this->sql, 'array');
        $mock->no_prepare();
        $mock->from('SqlSelect_executeReq', 'name')->where('id=1');
        $this->object($mock->executeReq())->isInstanceOf('\PDOStatement');
        
        //Test sans résultats
        $mock = new MockSqlSelect($this->sql, 'array');
        $mock->from('SqlSelect_executeReq', 'name')->where('id=2');
        $this->boolean($mock->executeReq())->isFalse();
        $this->boolean($mock->no_result)->isTrue();
        
        //Test avec exception
        $mock = new MockSqlSelect($this->sql, 'array');
        $this->exception(function() use($mock)
        {
            $mock->from('SqlSelect_executeReq', 'name')->where('desc="mon test"');
            $mock->executeReq();
        })->hasMessage('You have an error in your SQL syntax; check the manual that corresponds to your MySQL server version for the right syntax to use near \'desc="mon test"\' at line 1');
    }
    
    /**
     * Test de la méthode fetchRow()
     */
    public function testFetchRow()
    {
        //Création d'une table et ajout de valeur pour tester
        $this->sql->query('
            DROP TABLE IF EXISTS SqlSelect_fetchtRow;
            CREATE TABLE IF NOT EXISTS SqlSelect_fetchtRow
            (
                 `id` INTEGER UNSIGNED NOT NULL,
                 `name` VARCHAR(255),
                 PRIMARY KEY (`id`)
            ) ENGINE=MyISAM;
            
            TRUNCATE TABLE SqlSelect_fetchtRow;
            INSERT INTO SqlSelect_fetchtRow VALUES(1, "monTest 1");
        ');
        
        //Test avec un résultat sous forme objet
        $result = new \stdClass;
        $result->name = 'monTest 1';
        
        $mock = new MockSqlSelect($this->sql, 'objet');
        $mock->from('SqlSelect_fetchtRow', 'name')->where('id=:id', array(':id' => 1));
        $this->object($mock->fetchRow())->isEqualTo($result);
        
        $mock = new MockSqlSelect($this->sql, 'object');
        $mock->from('SqlSelect_fetchtRow', 'name')->where('id=:id', array(':id' => 1));
        $this->object($mock->fetchRow())->isEqualTo($result);
        
        //Test avec un résultat sous forme array
        $mock = new MockSqlSelect($this->sql, 'array');
        $mock->from('SqlSelect_fetchtRow', 'name')->where('id=:id', array(':id' => 1));
        $this->array($mock->fetchRow())->isEqualTo(array('name' => 'monTest 1'));
        
        //Test sans résultat
        $mock = new MockSqlSelect($this->sql, 'array');
        $mock->from('SqlSelect_fetchtRow', 'name')->where('id=:id', array(':id' => 2));
        $this->boolean($mock->fetchRow())->isFalse();
    }
    
    /**
     * Test de la méthode fetchAll()
     */
    public function testFetchAll()
    {
        //Création d'une table et ajout de valeur pour tester
        $this->sql->query('
            DROP TABLE IF EXISTS SqlSelect_fetchtAll;
            CREATE TABLE IF NOT EXISTS SqlSelect_fetchtAll
            (
                 `id` INTEGER UNSIGNED NOT NULL,
                 `name` VARCHAR(255),
                 PRIMARY KEY (`id`)
            ) ENGINE=MyISAM;
            
            TRUNCATE TABLE SqlSelect_fetchtAll;
            INSERT INTO SqlSelect_fetchtAll VALUES(1, "monTest 1");
            INSERT INTO SqlSelect_fetchtAll VALUES(2, "monTest 2");
            INSERT INTO SqlSelect_fetchtAll VALUES(3, "monTest 3");
        ');
        
        //Test avec un résultat sous forme objet
        $result1 = new \stdClass;
        $result1->name = 'monTest 1';
        $result2 = new \stdClass;
        $result2->name = 'monTest 2';
        $result3 = new \stdClass;
        $result3->name = 'monTest 3';
        $result = array($result1, $result2, $result3);
        
        $mock = new MockSqlSelect($this->sql, 'objet');
        $mock->from('SqlSelect_fetchtAll', 'name');
        $this->array($mock->fetchAll())->isEqualTo($result);
        
        $mock = new MockSqlSelect($this->sql, 'object');
        $mock->from('SqlSelect_fetchtAll', 'name');
        $this->array($mock->fetchAll())->isEqualTo($result);
        
        //Test avec un résultat sous forme array
        $mock = new MockSqlSelect($this->sql, 'array');
        $mock->from('SqlSelect_fetchtAll', 'name');
        $this->array($mock->fetchAll())->isEqualTo(array(
            array('name' => 'monTest 1'),
            array('name' => 'monTest 2'),
            array('name' => 'monTest 3')
        ));
        
        //Test sans résultat
        $mock = new MockSqlSelect($this->sql, 'array');
        $mock->from('SqlSelect_fetchtAll', 'name')->where('name="test"');
        $this->boolean($mock->fetchAll())->isFalse();
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
            DROP TABLE IF EXISTS SqlSelect_nbResult;
            CREATE TABLE IF NOT EXISTS SqlSelect_nbResult
            (
                 `id` INTEGER UNSIGNED NOT NULL,
                 `name` VARCHAR(255),
                 PRIMARY KEY (`id`)
            ) ENGINE=MyISAM;
            
            TRUNCATE TABLE SqlSelect_nbResult;
            INSERT INTO SqlSelect_nbResult VALUES(1, "monTest 1");
        ');
        
        //Test d'une requête devant retourner 1 résultat
        $req = $this->sql->query('SELECT name FROM SqlSelect_nbResult WHERE id=1');
        $this->mock->req = $req;
        $this->integer($this->mock->nb_result())->isEqualTo(1);
    }
}


/**
 * Mock pour la classe SqlSelect
 * 
 * @package bfw-sql\test
 */
class MockSqlSelect extends \BFWSql\SqlSelect
{
    /**
     * Accesseur get
     */
    public function __get($name) {return $this->$name;}
    
    /**
     * Accesseur set
     */
    public function __set($name, $value) {$this->$name = $value;}
    
    /**
     * Mock pour la méthode privé executeReq
     * 
     * @throws \Exception Si la requête à echoué
     * 
     * @return \PDOStatement|bool : La ressource de la requête exécuté si elle a réussi, false sinon.
     */
    public function executeReq()
    {
        return parent::executeReq();
    }
}