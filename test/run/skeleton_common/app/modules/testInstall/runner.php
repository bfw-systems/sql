<?php

$config = $module->getConfig();
$linker = \BFW\ControllerRouterLink::getInstance();
$linker->setTarget($config->getConfig('target'));
