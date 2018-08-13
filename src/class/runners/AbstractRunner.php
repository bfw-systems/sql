<?php

namespace BfwSql\Runners;

/**
 * Abstract class to define properties and methods used by all runners.
 * 
 * @package bfw-sql
 * @author Vermeulen Maxime <bulton.fr@gmail.com>
 * @version 2.0
 */
abstract class AbstractRunner
{
    /**
     * @var \BFW\Module Current bfw-sql module instance
     */
    protected $module;
    
    /**
     * Construct
     * 
     * @param \BFw\Module $module The bfw-sql module instance
     */
    public function __construct(\BFw\Module $module)
    {
        $this->module = $module;
    }
    
    /**
     * Getter accessor to module property
     * 
     * @return \BFW\Module
     */
    public function getModule(): \BFW\Module
    {
        return $this->module;
    }
    
    /**
     * Run the system
     * 
     * @return void
     */
    public abstract function run();
}
