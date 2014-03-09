<?php
/**
 * Classes en rapport avec les modèles
 * @author Vermeulen Maxime <bulton.fr@gmail.com>
 * @version 1.0
 */

namespace BFWSql;
 
/**
 * Gestion des modèles
 * @package bfw-sql
 */
abstract class Modeles extends \BFWSql\Sql implements \BFWSqlInterface\IModeles
{
    /**
     * @var $_name : Le nom de la table
     */
    protected $_name = '';
    
    /**
     * @var $DB : L'instace $Sql_connect qui gère la connexion vers la sgdb
     */
    protected $DB;
    
    /**
     * Consntructeur: Récupère la connexion Sql_connect
     */
    public function __construct()
    {
        global $DB;
        $this->DB = &$DB;
        parent::__construct($DB);
        
        if($this->_name != '')
        {
            parent::set_modeleName($this->_name);
        }
    }
}
