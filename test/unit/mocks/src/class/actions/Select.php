<?php

namespace BfwSql\Actions\Test\Mocks;

class Select extends \BfwSql\Actions\Select
{
    public function setReturnType($returnType)
    {
        $this->returnType = $returnType;
    }

    public function setMainTable($mainTable)
    {
        $this->mainTable = $mainTable;
    }

    public function setSubQueries($subQueries)
    {
        $this->subQueries = $subQueries;
    }

    public function setJoin($join)
    {
        $this->join = $join;
    }

    public function setJoinLeft($joinLeft)
    {
        $this->joinLeft = $joinLeft;
    }

    public function setJoinRight($joinRight)
    {
        $this->joinRight = $joinRight;
    }

    public function setOrder($order)
    {
        $this->order = $order;
    }

    public function setLimit($limit)
    {
        $this->limit = $limit;
    }

    public function setGroup($group)
    {
        $this->group = $group;
    }
    
    public function resetProperties()
    {
        $this->columns    = [];
        $this->where      = [];
        $this->mainTable  = null;
        $this->subQueries = [];
        $this->join       = [];
        $this->joinLeft   = [];
        $this->joinRight  = [];
        $this->order      = [];
        $this->limit      = '';
        $this->group      = [];
    }
    
    //---- Setter for AbstractActions ----\\
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
}