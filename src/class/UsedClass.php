<?php

namespace BfwSql;

/**
 * To obtain the class name to used for class declared into a config file
 * 
 * @package bfw-sql
 * @author Vermeulen Maxime <bulton.fr@gmail.com>
 * @version 2.0
 */
class UsedClass
{
    /**
     * @var \BFW\Application|null $instance Application instance (Singleton)
     */
    protected static $instance;
    
    /**
     * @var \BFW\Config $config The config instance for the current module
     */
    protected $config;
    
    /**
     * protected for Singleton pattern
     * 
     * @param \BFW\Config $config The config instance for the current module
     */
    protected function __construct(\BFW\Config $config)
    {
        $this->config = $config;
    }
    
    /**
     * Get the Application instance (Singleton pattern)
     * 
     * @param \BFW\Config|null $config The config instance for the module
     * 
     * @return \BfwSql\UsedClass
     */
    public static function getInstance($config = null)
    {
        if (self::$instance === null) {
            $class = get_called_class();
            self::$instance = new $class($config);
        }
        
        return self::$instance;
    }
    
    /**
     * Getter accessor to config property
     * 
     * @return \BFW\Config
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Obtain the class name declared into config file "class.php" for a key
     * 
     * @param string $classNameKey The class name key into the config file
     * 
     * @return string
     */
    public function obtainClassNameToUse($classNameKey)
    {
        return $this->config->getValue($classNameKey, 'class.php');
    }
}
