# Queries\Update

You can generate the majority of your Update queries with this class.
Like I explain before, Queries class generate their queries with many Parts classes.

## Access to each parts :

The list of all parts available is : 
* From AbstractQuery
  * `table` : Datas about the main table (name and shortcut) and columns to edit
  * `where` : List of all condition to use into WHERE section
* Into Insert
  * `from` : A reference to `table`
  * `join` : A list of table to join (with INNER JOIN) who contain table infos (name, shortcut and "on" condition) and their columns to return
  * `joinLeft` : A list of table to join (with LEFT JOIN) who contain table infos (name, shortcut and "on" condition) and their columns to return
  * `joinRight` : A list of table to join (with RIGHT JOIN) who contain table infos (name, shortcut and "on" condition) and their columns to return

For all of thats, you have a dynamic method who exist :
* `\BfwSql\Queries\Update public table(string|array $nameInfos, string|array|null $columns=null)`
* `\BfwSql\Queries\Update from(string|array $nameInfos, string|array|null $columns=null)`
* `\BfwSql\Queries\Update public join(string|array $table, string $on, [string|array $columns=null])`
* `\BfwSql\Queries\Update public joinLeft(string|array $table, string $on, [string|array $columns=null])`
* `\BfwSql\Queries\Update public joinRight(string|array $table, string $on, [string|array $columns=null])`
* `\BfwSql\Queries\Update public where(string $expr, array|null $preparedParams = null)`

Notes :
* methods `table` and `from` sent to the same instance. Like I said, it's a ref.
* For methods `table`, `from`, `join`, `joinLeft` and `joinRight` :
  * The first parameters for the table name can be a string with just the table name without prefix,
or an array where the key is the shortcut, and the value the table name without prefix.
  * The last parameters is for columns you want update for this table.
The value can be `null` to return nothing from this table, a string if we want return just on column, or an array of columns to return.
To add a shortcut to the column, the shortcut should be the key of the array, and the column the value.
* If your column value should be `null`, you can use the `null` php value.

## Executers

This class use the default executer `\BfwSql\Executer\Common` who will just execute the query.

To acces to the executer, use the getter `getExecuter()`
For more details about this executers, you can read the dedicated page.

In case of not select query, who not need to have a direct access to the executer.
You can just use the method `execute()` who are into Queries classes to execute the query (via the Executer) and obtain the number of rows impacted.

## Quoting system

A system for automaticaly quote datas is implemented. You can find more details about that into the dedicated page.
However, to talk about it quickly, you can define the default action the system can do
with the value of the first parameter of the `update` method.

By default, the system will quote all datas who are a string.

You can access to the quoting system instance by the getter `getQuoting()` of the Insert object.

## Example

I know, they are not great examples. If you have better, you can suggest others ;)

```php
namespace Modeles;

class MyTable
{
    protected $tableName = 'myTable';

    public function foo(array $datas, int $id): int
    {
        return $this->update()
            ->from($this->tableName, $datas)
            ->where('id=:id', [':id' => $id])
            ->execute()
        ;
        
        /*
         * If $datas = ['user' => 'bulton-fr']
         *
         * So the generated request is :
         * UPDATE `myTable` SET `myTable`.`user`='bulton-fr' WHERE id=:id
         */
    }
    
    public function bar(array $datas, int $id): int
    {
        return $this->update()
            ->from(['t' => $this->tableName], $datas)
            ->join(
                ['o' => 'myOtherTable'],
                'o.id=t.idother',
                'colA' => 'bar'
            )
            ->where('id=:id', [':id' => $id])
            ->execute()
        ;
        
        /*
         * If $datas = ['user' => 'bulton-fr']
         *
         * So the generated request is :
         * UPDATE `myTable`
         * SET
         *     `t`.`user`='bulton-fr',
         *     `o`.`colA`='bar'
         * INNER JOIN `myOtherTable` AS `o` ON o.id=t.idother
         * WHERE id=:id
         */
    }
}
```
