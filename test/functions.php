<?php

namespace BFWSql\test;

use \PDO;

function setMysqlUseBufferedQuery(PDO $PDO)
{
    if($PDO->getAttribute(PDO::ATTR_DRIVER_NAME) !== 'mysql')
    {
        return;
    }
    
    //Travis fail if attribute is not set.
    $PDO->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);
}
