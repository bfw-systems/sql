<?php

namespace BfwSql\Runners;

/**
 * Declare monolog instance for bfw-sql
 * It's a runner, so is called when the module is initialized.
 * 
 * @package bfw-sql
 * @author Vermeulen Maxime <bulton.fr@gmail.com>
 * @version 2.0
 */
class Monolog extends AbstractRunner
{
    /**
     * {@inheritdoc}
     * Run the system to generate the monolog instance
     */
    public function run()
    {
        $currentClass          = get_called_class();
        $this->module->monolog = $currentClass::createMonolog(
            $this->module->getConfig()
        );
        
        $this->module->monolog->addAllHandlers(
            'handlers',
            'monolog.php'
        );
    }
    
    /**
     * Create a new \BFW\Monolog instance
     * 
     * @param \BFW\Config $config
     * 
     * @return \BFW\Monolog
     */
    public static function createMonolog(\BFW\Config $config)
    {
        return new \BFW\Monolog(
            'bfw-sql',
            $config
        );
    }
}
