# Queries\AbstractQuery

All Queries classes extends this class. It define all common systems used by Queries classes.

## Properties

__`protected $sqlConnect`__

Contain the instance of `\BfwSql\SqlConnect` (contain the PDO instance) to use to execute queries.

__`protected $executer`__

Contain the executer instance to use to execute the generated query.

__`protected $assembledRequest`__

Contain the generated request who will be executed.

__`protected $preparedParams`__

All paramters will be pass to the executers if it's a prepared request.

__`protected $queriesParts`__

An array of all parts instance used for the request.

The default list is :
* `table` : `\BfwSql\Queries\Parts\Table`
* `where` : `\BfwSql\Queries\Parts\WhereList`

## Methods

__`self public __construct(\BfwSql\SqlConnect $sqlConnect)`__

The parameter `$sqlConnect` is the instance of `\BfwSql\SqlConnect` who match with the database where the query will be executed.


### Getters

__`string public getAssembledRequest()`__

__`\BfwSql\Executers\Common public getExecuter()`__

__`array public getPreparedParams()`__

__`array public getQueriesParts()`__

__`\BfwSql\SqlConnect public getSqlConnect()`__

### Dynamic methods

__`object public __call(string $name, array $args)`__

It's a [php magic method](http://php.net/manual/en/language.oop5.overloading.php#object.call) who is called (if declared) when the user call a method who not exist.
I use it to have an easy access to parts classes instances.

If the part asked class have the method `__invoke`, the `__invoke` method will be called (with passed arguments) and `__call` will return this own instance.
Else, the `__call` method will return the instance of the part object asked.

__`\BfwSql\Queries\AbstractQuery table(string|array $nameInfos, string|array|null $columns=null)`__

Corresponding to the part named `table` who have an instance of `\BfwSql\Queries\Parts\Table`.
This method not really exist and use the method `__call`.

__`\BfwSql\Queries\AbstractQuery where(string $expr, array|null $preparedParams = null)`__

Corresponding to the part named `where` who have an instance of `\BfwSql\Queries\Parts\WhereList`.
This method not really exist and use the method `__call`.

### Parts systems and assemble request

__`void protected defineQueriesParts()`__

Called by the method `__construct`, it define (and instance) all item will be into the property `$queriesParts`.

__`array protected abstract obtainGenerateOrder()`__

Obtain an array with the list of all part to use in the correct order to generate the final request.

Each value is an array who contains datas used for generation. The key list is :
* `prefix` : The value of the text to insert before the generated part.
For example, "SELECT", or "INSERT INTO", or "FROM", or "WHERE".
* `callback` : The callback to call to obtain generated value to insert.
If not define, the value will be the part instance and the method `generate`.
* `usePartPrefix` : If the `prefix` should be insered or not (because a space if automaticaly insert after the prefix).
* `canBeEmpty` : If the return of the callback can be empty or not.
For example, the return of the part "FROM" should not be empty.
If the return of the callback is empty and `canBeEmpty` is `false`, an `Exception` will be throw.

__`string public assemble([bool $force=false])`__

Generate the request with the method `assembleRequest` and returns it.
If the request has been already generated and if the parameter `$force` is `false`, the request will not be re-generated.

__`void protected assembleRequest()`__

Call the method `obtainGenerateOrder` to know the order or each item of the request,
and for each, call the method `assembleRequestPart` to generate the part and create the final request.

This also method check if the name of the main table as been declared.

__`string protected assembleRequestPart(string $partName, array $partInfos)`__

Generate and return the sql for a part (parameter `$partName`) of the final request.
For that, we pass to parameter `$partInfos` datas declared into the method `obtainGenerateOrder` for this part.

With the call to method `addMissingKeysToPartInfos` we adding missing datas into `$partInfos`.

If the key `callback` is defined, the callback will be called.
Throw an Exception if the `callback` returned data is empty while it should not be.

If the prefix is declared to be use, the prefix will be added before the sql generated with a space.

__`void protected addMissingKeysToPartInfos(string $partName, array &$partInfos)`__

Check array passed into parameter and come from the array returned by `obtainGenerateOrder` to add missing keys into it.
All missing keys will take values returned by the method `obtainPartInfosDefaultValues`.

__`\BfwSql\Queries\Parts\AbstractPart protected obtainPartInfosDefaultValues(string $partName)`__

Return the object containing all default value for a part infos.

If the part not exist into the array of the property `$queriesParts`, we use an anonymous class who
extend `\BfwSql\Queries\Parts\AbstractPart` to have all method and property with their default values.

__`bool public isAssembled()`__

Check if the final request has been assembled.

### Executed query

__`self public addPreparedParams(array $preparedParams)`__

Add parameters to the list of parameters used for prepared request.

__`\PDOStatement|int public execute()`__

Call the Executer who will execute the request.
The method will return the `\PDOStatement` instance for a Select request, or the number of row affected for others request type.
If an error is detected, an `Exception` will be throw by the executer (you will have to catch it).

__`self public query(string $request)`__

Execute an user request. The parameter is the request you have write and to execute.
