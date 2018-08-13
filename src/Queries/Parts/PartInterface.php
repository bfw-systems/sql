<?php

namespace BfwSql\Queries\Parts;

interface PartInterface
{
    /**
     * Generate the sql query for to the concerned part
     * 
     * @return string
     */
    public function generate(): string;
}
