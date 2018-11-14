<?php

namespace BfwSql\Queries\SGBD;

class Sqlite extends AbstractSGBD
{
    /**
     * {@inheritdoc}
     * 
     * Disable all joins for update queries with sqlite
     */
    protected function obtainPartsToDisable(): array
    {
        return [
            'delete' => [],
            'insert' => [],
            'select' => [],
            'update' => ['join', 'joinLeft', 'joinRight']
        ];
    }
    
    /**
     * {@inheritdoc}
     * 
     * Not add table name/shortcut before column name for an update query.
     */
    public function columnName(
        string $colName,
        string $tableName,
        bool $isFunction,
        bool $isJoker
    ): string {
        if ($this->obtainRequestType() === 'update') {
            if ($isFunction === true || $isJoker === true) {
                return $colName;
            } else {
                return '`'.$colName.'`';
            }
        }
        
        return parent::columnName($colName, $tableName, $isFunction, $isJoker);
    }
}
