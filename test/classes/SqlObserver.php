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
class SqlObserver extends atoum
{
    /**
     * @var $class : Instance de la class SqlObserver
     */
    protected $class;
    
    /**
     * Instanciation de la class avant chaque méthode de test
     */
    public function beforeTestMethod($testMethod)
    {
        $this->class = new \BFWSql\SqlObserver();
    }
    
    /**
     * Test de la méthode updateWithAction(BFW\Kernel $subject, $action)
     */
    public function testUpdateWithAction()
    {
        
    }
    
    /**
     * Test de la méthode update(SplSubject $subject)
     */
    public function testUpdate()
    {
        
    }
}
