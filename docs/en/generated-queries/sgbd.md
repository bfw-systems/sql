# Queries\SGBD

Because all SGBD is same, some query part cannot be used for some SGBD.
For example, you can do join on update query with mysql, but you cannot with sqlite.

To avoid query execution problems, a system has been implemented and integrated
in Queries\Parts to disallow the use of some query part with some SGBD.

Only some case has been implemented for the moment, if you see more case to add, please create an issue or a pull request. ;)

## How it works

There is the class `AbstractSGBD` who implement all default case.
And there is a class for each SGBD implemented by PDO.
All class extends from `AbstractSGBD` and override methods to disallow or change the way to generate the query part.

The class is instantiated by `\BfwSql\Queries\AbstractQuery`, and the method
`disableQueriesParts` is called by Query class at the end of the method `defineQueriesParts`.
With that, each part that must be disabled take the status disabled.

After that, during query write, if the user call a part which be disabled, an exception is thrown.
And during query generation, it's the SGBD method who generate the sql.
So many particular cases in query is managed.

## Write query methods

__`string protected obtainRequestType()`__

To know the current request type.
Values can be "delete", "insert", "select" or "update".

__`string public columnName(string $colName, string $tableName, bool $isFunction, bool $isJoker)`__

Used by `\BfwSql\Queries\Parts\Column::obtainName` to generate the column name to use.

Specific case :
* Sqlite : Remove the table name on an update query.

__`string public join(string $tableName, string|null $shortcut, string $on)`__

Used by `\BfwSql\Queries\Parts\Join::generate` to generate the join query part.

__`string public limit(int|null $rowCount, int|null $offset)`__

Used by `\BfwSql\Queries\Parts\Limit::generate` to generate the limit query part.

__`string public listItem(string $expr, int $index, string $separator)`__

Used by many list classes to generate the query part for the list.

Classes who use it are:
* `\BfwSql\Queries\Parts\AbstractList`
* `\BfwSql\Queries\Parts\ColumnList`
* `\BfwSql\Queries\Parts\ColumnValueList`
* `\BfwSql\Queries\Parts\JoinList`
* `\BfwSql\Queries\Parts\OrderList`
* `\BfwSql\Queries\Parts\SubQueryList`

__`string public order(string $expr, string|null $sort, bool $isFunction)`__

Used by `\BfwSql\Queries\Parts\Order::generate` to generate the order by query part.

__`string public subQuery(string $subQuery, string $shortcut)`__

Used by `\BfwSql\Queries\Parts\SubQuery::generate` to generate the query part for a sub-query.

__`string public table(string $name, string|null $shortcut)`__

Used by `\BfwSql\Queries\Parts\Table::generate` to generate the query part for the main table.
