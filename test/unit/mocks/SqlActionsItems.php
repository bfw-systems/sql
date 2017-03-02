<?php

namespace BfwSql\test\unit\mocks;

trait SqlActionsItems
{
    protected $observerCalled = false;
    
    public function __get($name)
    {
        return $this->{$name};
    }
    
    public function setQuoteStatus($newStatus)
    {
        $this->quoteStatus = $newStatus;
    }
    
    public function setQuotedColumns($newValue)
    {
        $this->quotedColumns = $newValue;
    }
    
    public function setNotQuotedColumns($newValue)
    {
        $this->notQuotedColumns = $newValue;
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
