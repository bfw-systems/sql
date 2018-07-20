<?php

namespace BfwSql;

$moduleConfig = $this->getConfig();
$usedClass    = \BfwSql\UsedClass::getInstance($moduleConfig);

$runnerClasses = [
    $usedClass->obtainClassNameToUse('RunnerMonolog'),
    $usedClass->obtainClassNameToUse('RunnerObservers'),
    $usedClass->obtainClassNameToUse('RunnerConnectDb')
];

foreach ($runnerClasses as $runnerClassName) {
    $runner = new $runnerClassName($this);
    $runner->run();
}
