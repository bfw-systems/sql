<?php

$modeleTests = new \Modeles\Tests;
$modeleTests->runTest();

echo "\n".'Status Execution (Http Code) : '.http_response_code()."\n";

