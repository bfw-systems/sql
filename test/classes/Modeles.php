<?php
/**
 * Classes de test en rapport avec les modèles
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
class Modeles extends atoum
{
    /**
     * @var $class : Instance de la class Modeles
     */
    protected $class;
    
    /**
     * Test du constructeur : Modeles()
     */
    public function testModeles()
    {
        //Test sans préfix
        $modeles = new MockModeles;
        $this->string($modeles->getRealName())->isEqualTo('test');
        
        //Modification du préfix pour en avoir un
        global $bd_prefix;
        $bd_prefix = 'prefix_'; //Préfix en début de table
        
        //Retest du préfix
        $modeles = new MockModeles;
        $this->string($modeles->getRealName())->isEqualTo('prefix_test');
    }
}

/**
 * Mock pour la classe abstraite Modeles
 * 
 * @package bfw-sql\test
 */
class MockModeles extends \BFWSql\Modeles
{
    /**
     * @var string $_name : Nom de la table
     */
    protected $_name = 'test';
    
    /**
     * @var array $_map : Les champs de la table
     */
    protected $_map = array(
        'idTest' => array('type' => 'int',    'default' => 0),
        'value'  => array('type' => 'string', 'default' => ''),
    );
    
    /**
     * @var $_columnID : Colonne représentant l'id
     */
    protected $_columnID = 'idTest';
    
    /**
     * Accesseur vers $_realName
     */
    public function getRealName() {return $this->_realName;}
}