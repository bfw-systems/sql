# Into a model

Like I said before, if your model extends the class `BfwSql\AbstractModels`, you will have access to many properties and methods.

## Properties

From `BfwSql\AbstractModels` :
* `protected $tableName` : The table name of this model
* `protected $tableNameWithPrefix` : The name with the table prefix
* `protected $baseKeyName` : The baseKeyName who match with the dabatase to use with this model

From `BfwSql\Sql` (because `AbstractModels` extend it) :
* `protected $sqlConnect` : The `BfwSql\SqlConnect` instance who match with the database to use
* `protected $prefix` : The prefix to use for all table of this connection

## Methods

From `BfwSql\AbstractModels` :
* Getters
  * `string public getBaseKeyName()`
  * `string public getTableName()`
  * `string public getTableNameWithPrefix()`
* `\BFW\Application protected obtainApp()`
To get a direct access to `\BFW\Application` instance
* `\BfwSql\SqlConnect protected obtainSqlConnect()`
To find the instance of `\BfwSql\SqlConnect` who match with the database to use

From `BfwSql\Sql` (because `AbstractModels` extend it) :
* Getters
  * `string public getPrefix()`
  * `\BfwSql\SqlConnect public getSqlConnect()`
* Queries system generator (more details into dedicated part)
  * `\BfwSql\Queries\Delete public delete()`
  * `\BfwSql\Queries\Insert public insert([string $quoteStatus="all"])`
  * `\BfwSql\Queries\Select public select([string $type="array"])`
  * `\BfwSql\Queries\Update public update([string $quoteStatus="all"])`
* `int public createId(string $table, string $column)`
Find the first vacant id on a table and for a column
* `int public obtainLastInsertedId([string|null $name=null])`
Get the id for the last item has been insert in database
* `int public obtainLastInsertedIdWithoutAI(string $table, string $colId, array $order, [string|array $where=""])`
Get the id for the last item has been insert in database for a table without auto-increment
* `\PDOStatement public query(string $request)`
Execute a query
