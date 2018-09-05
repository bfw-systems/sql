# Queries\Insert

You can generate the majority of your INSERT INTO queries with this class.
Like I explain before, Queries class generate their queries with many Parts classes.

## Access to each parts :

The list of all parts available is : 
* From AbstractQuery
  * `table` : Datas about the main table (name and shortcut) and columns to edit
  * `where` : List of all condition to use into WHERE section
* Into Insert
  * `into` : A reference to `table`
  * `values` : A reference to columns list into `table`
  * `select` : An instance of `Queries\Select` to write an `INSERT INTO .. SELECT` query
  * `onDuplicate` : A list to contain columns and their value to use for the `ON DUPLICATE KEY UPDATE` part

For all of thats, you have a dynamic method who exist :
* `\BfwSql\Queries\Insert public table(string|array $nameInfos, string|array|null $columns=null)`
* `\BfwSql\Queries\Insert public where(string $expr, array|null $preparedParams = null)`
* `\BfwSql\Queries\Insert into(string|array $nameInfos, string|array|null $columns=null)`
* `\BfwSql\Queries\Insert onDuplicate(array $columns)`
* `\BfwSql\Queries\Select select()`
* `\BfwSql\Queries\Insert values(array $columns)`

Notes :
* For methods `table` and `into` :
  * They return the same object instance. Like I said, it's a ref
  * The first parameters for the table name can be a string with just the table name without prefix,
or an array where the key is the shortcut, and the value the table name without prefix.
  * The last parameters is for columns values to insert for this table.
The value can be `null` to return nothing from this table, a string if we want return just on column, or an array of columns to return.
To add a shortcut to the column, the shortcut should be the key of the array, and the column the value.
* If you call `values` before `table` or `into`, the column list will not be erased.
* The columns list can be empty to add an empty line.
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
with the value of the first parameter of the `insert` method.

By default, the system will quote all datas who are a string.

You can access to the quoting system instance by the getter `getQuoting()` of the Insert object.

## Example

I know, they are not great examples. If you have better, you can suggest others ;)

```php
namespace Modeles;

class MyTable
{
    protected $tableName = 'myTable';

    public function foo(array $datas): int
    {
        return $this->insert()
            ->into($this->tableName, $datas)
            ->execute()
        ;
        
        /*
         * If $datas = ['user' => 'bulton-fr']
         *
         * So the generated request is :
         * INSERT INTO `myTable` (`user`) VALUES ('bulton-fr')
         */
    }
    
    public function bar(array $datas): int
    {
        return $this->insert()
            ->into($this->tableName, $datas)
            ->onDuplicate($data)
            ->execute()
        ;
        
        /*
         * If $datas = ['user' => 'bulton-fr']
         *
         * So the generated request is :
         * INSERT INTO `myTable`
         * (`user`) VALUES ('bulton-fr')
         * ON DUPLICATE KEY UPDATE `user`='bulton-fr'
         */
    }
    
    public function baz(): int
    {
        $query = $this
            ->insert()
                ->into(
                    $this->tableName,
                    [
                        'colA' => null,
                        'colB' => null
                    ]
                )
        ;
        
        $query->select()
            ->from('myOtherTable', ['colA', 'colB'])
            
        return $query->execute();
        
        /*
         * The generated request is :
         * INSERT INTO `myTable` (`colA`, `colB`)
         * SELECT `myOtherTable`.`colA`, `myOtherTable`.`colB`
         * FROM `myOtherTable`
         */
    }
}
```
