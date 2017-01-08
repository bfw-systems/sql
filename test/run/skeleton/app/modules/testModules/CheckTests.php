<?php

namespace Modules\testModules;

use \Exception;

trait CheckTests
{
    use \Modules\testModules\tests\TestSql;
    use \Modules\testModules\tests\TestSqlSelect;
    use \Modules\testModules\tests\TestSqlInsert;
    use \Modules\testModules\tests\TestSqlUpdate;
    use \Modules\testModules\tests\TestSqlDelete;
    use \Modules\testModules\tests\TestHelperSecure;
    
    protected $testName = '';
    protected $testStatus = true;
    
    public function runTest()
    {
        echo 'Create db table'."\n";
        $this->sqlConnect->getPDO()->exec(
            'DROP TABLE IF EXISTS `test_runner`;'
            .'CREATE TABLE `test_runner` ('
                .'`id`  int UNSIGNED NOT NULL AUTO_INCREMENT ,'
                .'`title`  varchar(255) NOT NULL ,'
                .'`date`  datetime NOT NULL ,'
                .'`enabled`  tinyint(1) NOT NULL DEFAULT 0 ,'
                .'PRIMARY KEY (`id`)'
            .');'
        );
        
        try {
            $this->testSqlRun();
            $this->testSqlSelectRun();
            $this->testSqlInsertRun();
            $this->testSqlUpdateRun();
            $this->testSqlDeleteRun();
            $this->testHelperSecureRun();
        } catch (Exception $e) {
            echo "\n\033[1;31m".$e->getMessage()."\033[0m\n";
        }
    }
    
    protected function newTest($testName)
    {
        $this->testName = $testName;
        echo ' > '.$testName."\n";
    }
    
    protected function checkTest($callback)
    {
        $testReturn = $callback();
        if ($testReturn === true) {
            return;
        }
        
        $this->testStatus = false;
        http_response_code(500);
        
        throw new Exception('Test fail on '.$this->testName);
    }
}
