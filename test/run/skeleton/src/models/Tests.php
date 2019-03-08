<?php

namespace Models;

class Tests extends \BfwSql\AbstractModels
{
    use \Modules\testModules\CheckTests;
    
    protected $tableName = 'runner';
}
