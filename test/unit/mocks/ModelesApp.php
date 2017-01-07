<?php

namespace BfwSql\test\unit\mocks;

class ModelesApp
{
    protected static $instance;
    
    protected $listBases;
    
    protected function __construct($listBases)
    {
        $this->listBases = $listBases;
    }
    
    public static function getInstance($listBases = null)
    {
        if (self::$instance === null) {
            $calledClass = get_called_class();
            self::$instance = new $calledClass($listBases);
        }

        return self::$instance;
    }
    
    public function getModule($moduleName)
    {
        return (object) [
            'listBases' => $this->listBases
        ];
    }
}
