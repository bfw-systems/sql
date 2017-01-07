<?php

namespace BfwSql\test\unit\mocks;

trait SqlActionsItems
{
    protected $observerCalled = false;
    
    public function __get($name)
    {
        return $this->{$name};
    }

    protected function callObserver()
    {
        $this->observerCalled = true;
    }
    
    public function callAssembleRequest()
    {
        parent::assembleRequest();
        return $this->assembledRequest;
    }
}
