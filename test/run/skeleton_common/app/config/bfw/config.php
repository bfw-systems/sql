<?php

$config = require(__DIR__.'/config.php.original');

$config['modules']['controller']['name']    = 'bfw-controller';
$config['modules']['controller']['enabled'] = true;

return $config;
