<?php

namespace BfwSql\Test\Mocks;

class SqlConnect extends \BfwSql\SqlConnect
{
    public function setPDO(\PDO $PDO)
    {
        $this->PDO = $PDO;
    }

    public function setConnectionInfos($connectionInfos)
    {
        $this->connectionInfos = $connectionInfos;
    }

    public function setType($type)
    {
        $this->type = $type;
    }

    public function setNbQuery($nbQuery)
    {
        $this->nbQuery = $nbQuery;
    }
}
