<?php

namespace BfwSql;

$moduleConfig = $this->getConfig();
$usedClass    = \BfwSql\UsedClass::getInstance($moduleConfig);

\BFW\Helpers\Constants::create('MODELS_DIR', SRC_DIR.'models/');
\BFW\Application::getInstance()
    ->getComposerLoader()
    ->addPsr4('Models\\', MODELS_DIR)
;

$runnerClasses = [
    $usedClass->obtainClassNameToUse('RunnerMonolog'),
    $usedClass->obtainClassNameToUse('RunnerObservers'),
    $usedClass->obtainClassNameToUse('RunnerConnectDB')
];

foreach ($runnerClasses as $runnerClassName) {
    $runner = new $runnerClassName($this);
    $runner->run();
}
