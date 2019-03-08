# Queries\Delete

You can generate the majority of your DELETE FROM queries with this class.
Like I explain before, Queries class generate their queries with many Parts classes.

## Access to each parts :

The list of all parts available is : 
* From AbstractQuery
  * `table` : Datas about the main table (name and shortcut) and columns to return for this table
  * `where` : List of all condition to use into WHERE section
* Into Delete
  * `from` : A reference to `table`

For all of thats, you have a dynamic method who exist :
* `\BfwSql\Queries\Select public from(string|array $nameInfos, string|array|null $columns=null)`
* `\BfwSql\Queries\Select public where(string $expr, array|null $preparedParams = null)`

Notes :
* methods `table` and `from` sent to the same instance. Like I said, it's a ref.
* For methods `table`, `from`, `join`, `joinLeft` and `joinRight` :
  * The first parameters for the table name can be a string with just the table name without prefix,
or an array where the key is the shortcut, and the value the table name without prefix.
  * The last parameters is useless here. This default value is null, so you can forget it.

## Executers

This class use the default executer `\BfwSql\Executer\Common` who will just execute the query.

To acces to the executer, use the getter `getExecuter()`
For more details about this executers, you can read the dedicated page.

In case of not select query, who not need to have a direct access to the executer.
You can just use the method `execute()` who are into Queries classes to execute the query (via the Executer) and obtain the number of rows impacted.

## Example

I will not create an example for all methods available.
For missing example, I think parameters name can be enough to understand.
But if you have some examples to add, I will be happy to add them.

```php
namespace Models;

class MyTable
{
    protected $tableName = 'myTable';

    public function removeAll(): int
    {
        return $this
            ->update()
                ->from($this->tableName)
            ->execute()
        ;
        
        //Generated request is :
        //DELETE FROM `myTable`
    }
    
    public function removeId(int $id): int
    {
        return $this
            ->delete()
                ->from($this->tableName)
                ->where('id=:id', [':id' => $id])
            ->execute()
        ;
        
        /*
         * Generated request is :
         * 
         * DELETE FROM `myTable` WHERE id=:id
         */
    }
}
```
