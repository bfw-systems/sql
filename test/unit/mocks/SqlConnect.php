<?php

namespace BfwSql\test\unit\mocks;

class SqlConnect extends \BfwSql\SqlConnect
{
    public function __get($name)
    {
        return $this->{$name};
    }
    
    protected function createConnection()
    {
        $this->PDO = new \BfwSql\test\unit\mocks\PDO;
    }
}
