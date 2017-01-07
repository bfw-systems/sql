<?php

namespace BfwSql\test\unit\mocks;

class Modeles extends \BfwSql\Modeles
{
    protected $listBases;
    
    public function __construct()
    {
        //Nothing here. Not call parent construct !
    }
    
    public function __get($name)
    {
        return $this->{$name};
    }
    
    public function __set($name, $value)
    {
        $this->{$name} = $value;
        return $this;
    }
    
    public function setListBases(&$listBases)
    {
        $this->listBases = $listBases;
    }
    
    public function callParentConstructor()
    {
        return parent::__construct();
    }
    
    protected function getApp()
    {
        return ModelesApp::getInstance($this->listBases);
    }
}
