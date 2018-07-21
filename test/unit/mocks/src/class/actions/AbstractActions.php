<?php

namespace BfwSql\Actions\Test\Mocks;

class AbstractActions extends \BfwSql\Actions\AbstractActions
{
    public function setSqlConnect(\BfwSql\SqlConnect $sqlConnect)
    {
        $this->sqlConnect = $sqlConnect;
    }

    public function setAssembledRequest($assembledRequest)
    {
        $this->assembledRequest = $assembledRequest;
    }

    public function setTableName($tableName)
    {
        $this->tableName = $tableName;
    }

    public function setColumns($columns)
    {
        $this->columns = $columns;
    }

    public function setQuoteStatus($quoteStatus)
    {
        $this->quoteStatus = $quoteStatus;
    }

    public function setQuotedColumns($quotedColumns)
    {
        $this->quotedColumns = $quotedColumns;
    }

    public function setNotQuotedColumns($notQuotedColumns)
    {
        $this->notQuotedColumns = $notQuotedColumns;
    }

    public function setWhere($where)
    {
        $this->where = $where;
    }

    public function setPreparedRequestArgs($preparedRequestArgs)
    {
        $this->preparedRequestArgs = $preparedRequestArgs;
    }

    public function setNoResult($noResult)
    {
        $this->noResult = $noResult;
    }

    public function setLastRequestStatement($lastRequestStatement)
    {
        $this->lastRequestStatement = $lastRequestStatement;
    }

    public function setLastErrorInfos($lastErrorInfos)
    {
        $this->lastErrorInfos = $lastErrorInfos;
    }
    
    protected function assembleRequest()
    {
        //Nothing to do
    }
}