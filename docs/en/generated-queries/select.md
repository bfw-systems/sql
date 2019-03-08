# Queries\Select

You can generate the majority of your SELECT queries with this class.
Like I explain before, Queries class generate their queries with many Parts classes.

When you call the `select()` method into your model, you can choose the format for the returned datas.
By default is "array", but you can switch to "object".

## Access to each parts :

The list of all parts available is : 
* From AbstractQuery
  * `table` : Datas about the main table (name and shortcut) and columns to return for this table
  * `where` : List of all condition to use into WHERE section
* Into Select
  * `subQuery` : List of all sub-query (and their shortcut) to add into SELECT section
  * `from` : A reference to `table`
  * `join` : A list of table to join (with INNER JOIN) who contain table infos (name, shortcut and "on" condition) and their columns to return
  * `joinLeft` : A list of table to join (with LEFT JOIN) who contain table infos (name, shortcut and "on" condition) and their columns to return
  * `joinRight` : A list of table to join (with RIGHT JOIN) who contain table infos (name, shortcut and "on" condition) and their columns to return
  * `order` : A list of expression to add into ORDER BY section
  * `limit` : The number of rows and the offset to use into the LIMIT section
  * `group` : A list of expression to add into GROUP BY section

For all of thats, you have a dynamic method who exist :
* `\BfwSql\Queries\Select public subQuery(string $shortcut, string|\BfwSql\Queries\AbstractQuery $subQuery)`
* `\BfwSql\Queries\Select public table(string|array $nameInfos, string|array|null $columns=null)`
* `\BfwSql\Queries\Select public from(string|array $nameInfos, string|array|null $columns=null)`
* `\BfwSql\Queries\Select public join(string|array $table, string $on, [string|array $columns=null])`
* `\BfwSql\Queries\Select public joinLeft(string|array $table, string $on, [string|array $columns=null])`
* `\BfwSql\Queries\Select public joinRight(string|array $table, string $on, [string|array $columns=null])`
* `\BfwSql\Queries\Select public where(string $expr, array|null $preparedParams = null)`
* `\BfwSql\Queries\Select public group(string $expr)`
* `\BfwSql\Queries\Select public limit([int $offset,] int $rowCount)`
* `\BfwSql\Queries\Select public order(string $expr, string|null $sort = 'ASC')`

Notes :
* methods `table` and `from` sent to the same instance. Like I said, it's a ref.
* For methods `table`, `from`, `join`, `joinLeft` and `joinRight` :
  * The first parameters for the table name can be a string with just the table name without prefix,
or an array where the key is the shortcut, and the value the table name without prefix.
  * The last parameters is for columns to return for this table.
The value can be `null` to return nothing from this table, a string if we want return just on column, or an array of columns to return.
To add a shortcut to the column, the shortcut should be the key of the array, and the column the value.

## Executers

This class define an executer different from the others to access to fetch methods.

To acces to the executer, use the getter `getExecuter()`, it will return you an `\BfwSql\Executers\Select` object.
For more details about this executers, you can read the dedicated page.

You have access to two fetch methods :
* \Generator public fetchAll() : Return a generator (yield) of all lines returned
* mixed public fetchRow() : Return the readed line

Fetch methods will execute the query if it doesn't.
If there is an error during execution, an `Exception` will be throw.

## Example

I will not create an example for all methods available.
For missing example, I think parameters name can be enough to understand.
But if you have some examples to add, I will be happy to add them.

```php
namespace Models;

class MyTable
{
    protected $tableName = 'myTable';

    public function getAll(): \Generator
    {
        $query = $this->select()->from($this->tableName, '*');
        
        return $query->getExecuter()->fetchAll();
        
        //Generated request is :
        //SELECT `myTable`.* FROM `myTable`
    }
    
    public function getForId(int $id)
    {
        $query = $this
            ->select()
                ->from($this->tableName, '*')
                ->where('id=:id', [':id' => $id])
        ;
        
        return $query->getExecuter()->fetchRow();
        
        /*
         * Generated request is :
         * 
         * SELECT `myTable`.*
         * FROM `myTable`
         * WHERE id=:id
         */
    }
    
    public function foo(int $id)
    {
        $query = $this
            ->select()
                ->from(['t' => $this->tableName], '*')
                ->where('id=:id', [':id' => $id])
        ;
        
        return $query->getExecuter()->fetchRow();
        
        /*
         * Generated request is :
         * 
         * SELECT `t`.*
         * FROM `myTable` AS `t`
         * WHERE id=:id
         */
    }

    public function bar(int $id)
    {
        $query = $this
            ->select()
                ->from(['t' => $this->tableName], ['a' => 'colA'])
                ->where('id=:id', [':id' => $id])
        ;
        
        return $query->getExecuter()->fetchRow();
        
        /*
         * Generated request is :
         * 
         * SELECT `t`.`colA` AS `a`
         * FROM `myTable` AS `t`
         * WHERE id=:id
         */
    }

    public function baz(int $id): \Generator
    {
        $query = $this
            ->select()
                ->from(
                    ['t' => $this->tableName],
                    [
                        'a' => 'colA',
                        'b' => 'colB'
                    ]
                )
                ->join(
                    ['baz' => 'tableBaz'],
                    'baz.id=t.idbaz',
                    ['c' => 'colC']
                )
                ->where('t.id=:id', [':id' => $id])
        ;
        
        return $query->getExecuter()->fetchAll();
        
        /*
         * Generated request is :
         * 
         * SELECT `t`.`colA` AS `a`, `t`.`colB` AS `b`, `baz`.`colC` AS `c`
         * FROM `myTable` AS `t`
         * INNER JOIN `tableBaz` AS `baz` ON baz.id=t.idbaz
         * WHERE id=:id
         */
    }
}
```
