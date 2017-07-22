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
        try {
            //$this->debugTable();
            $this->testSqlRun();
            $this->testSqlSelectRun();
            $this->testSqlInsertRun();
            $this->testSqlUpdateRun();
            $this->testSqlDeleteRun();
            $this->testHelperSecureRun();
        } catch (Exception $e) {
            http_response_code(500);
            echo "\n\033[1;31m".$e->getMessage()."\033[0m\n";
        }
    }
    
    protected function debugTable()
    {
        echo 'List existing table for user root :'."\n";
        echo shell_exec('mysql -e "SHOW TABLES;" -u root -D bfw_sql_tests');
        echo "\n";
        
        echo 'List existing table for user travis :'."\n";
        echo shell_exec('mysql -e "SHOW TABLES;" -u travis -D bfw_sql_tests');
        echo "\n";
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
    
    protected function runExec($query)
    {
        $execReturn = $this->sqlConnect->getPDO()->exec($query);
        
        if ($execReturn !== false) {
            return $execReturn;
        }
        
        $error = $this->sqlConnect->getPDO()->errorInfo();
        throw new Exception(
            'SQL error during test '.$this->testName
            ."\nQuery : ".$query
            ."\nError :".$error[2]
        );
    }
}
