<?php

$modelTests = new \Models\Tests;
$modelTests->runTest();

echo "\n".'Status Execution (Http Code) : '.http_response_code()."\n";

