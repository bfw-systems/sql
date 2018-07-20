<?php

namespace BfwSql\Runners;

/**
 * Connect to all declared databases
 * It's a runner, so is called when the module is initialized.
 * 
 * @package bfw-sql
 * @author Vermeulen Maxime <bulton.fr@gmail.com>
 * @version 2.0
 */
class ConnectDB extends AbstractRunner
{
    /**
     * @const ERR_NO_CONNECTION_KEYNAME Exception code if the connection has no
     * keyName and there are many connection declared.
     */
    const ERR_NO_CONNECTION_KEYNAME = 2502001;
    
    /**
     * @const ERR_NO_BASE_TYPE Exception code if the base type is not declared.
     */
    const ERR_NO_BASE_TYPE = 2502002;
    
    /**
     * {@inheritdoc}
     * Run the system to connect to all database declared.
     */
    public function run()
    {
        $moduleConfig    = $this->module->getConfig();
        $configListBases = $moduleConfig->getValue('bases', 'bases.php');
        $configNbBases   = count($configListBases);
        
        $this->module->listBases = [];

        foreach ($configListBases as $baseInfos) {
            $this->connectToDatabase($baseInfos);
        }
    }
    
    /**
     * Create the connection to a database
     * 
     * @param \stdClass $baseInfos Information about the database to connect
     * 
     * @throws \Exception
     * 
     * @return void
     */
    protected function connectToDatabase($baseInfos)
    {
        if (empty($baseInfos->baseType)) {
            throw new \Exception(
                'bfw-sql : All connection should have a type declared.',
                self::ERR_NO_BASE_TYPE
            );
        }

        $baseKey         = $baseInfos->baseKeyName;
        $moduleConfig    = $this->module->getConfig();
        $configListBases = $moduleConfig->getValue('bases', 'bases.php');
        $configNbBases   = count($configListBases);

        if (empty($baseKey) && $configNbBases > 1) {
            throw new \Exception(
                'bfw-sql : They are multiple connection defined,'
                .' a keyName must be define to each connection.',
                self::ERR_NO_CONNECTION_KEYNAME
            );
        }

        $usedClass        = \BfwSql\UsedClass::getInstance();
        $connectClassName = $usedClass->obtainClassNameToUse('SqlConnect');
        
        $this->module->listBases[$baseKey] = new $connectClassName($baseInfos);
        $this->module->listBases[$baseKey]->createConnection();
    }
}
