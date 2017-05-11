<?php

$moduleConfig    = $this->getConfig();
$configListBases = $moduleConfig->getValue('bases');
$configNbBases   = count($configListBases);
$this->listBases = [];

$observerConfig  = $moduleConfig->getValue('observer');
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
    
    $this->listBases[$baseKey] = new \BfwSql\SqlConnect($baseInfos);
}
