<?php

$moduleConfig      = $module->getConfig();
$configListBases   = $moduleConfig->getConfig('bases');
$configNbBases     = count($configListBases);
$module->listBases = [];

$observerConfig  = $moduleConfig->getConfig('observer');
$observerLogFile = $observerConfig->logFile;

if (
    $observerConfig->enable === true
    && !empty($observerLogFile)
) {
    $observer = new \BfwSql\SqlObserver($observerConfig);
    \BFW\Application::getInstance()->attach($observer);
}

foreach ($configListBases as $baseInfos) {
    if (empty($baseInfos->baseType)) {
        continue;
    }
    
    $baseKey = $baseInfos->baseKeyName;

    if (empty($baseKey) && $configNbBases > 1) {
        throw new \Exception(
            'bfw-sql : They are multiple connexion defined,'
            .' a keyName must be define to each connexion.'
        );
    }
    
    $module->listBases[$baseKey] = new \BfwSql\SqlConnect($baseInfos);
}
