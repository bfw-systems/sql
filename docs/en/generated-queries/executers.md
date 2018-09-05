# Executers classes

Queries classes is only to generate a query. It's Executers classes who execute it into the database.
Executers classes are instantiate into `Queries\AbstractQuery` constructor and take the Queries instance into its own constructor.

There are two Executer classes:
* Common : For execute all request type
* Select : To have fetch methods (extends Common)

## Executers\Common

__`self public __construct(\BfwSql\Queries\AbstractQuery $query)`__

Define property `$query` with the Queries instance,
and property `$sqlConnect` with the `\SqlConnect` instance which was defined into Query constructor.

__`bool public getIsPreparedRequest()`__<br>
__`self public setIsPreparedRequest(bool $preparedRequestStatus)`__

Getter and setter to know/define if the system will use prepared request or not.
By default, the system will always use prepared request.

__`array public getLastErrorInfos()`__

Contain the return of the method `PDO::errorInfo()` who called after the request's execution.

__`\PDOStatement|int|bool|null public getLastRequestStatement()`__

Contain the return of methods `POD::exec`, `PDO::query` or `PDO::prepare` used to execute the request.

__`bool public getNoResult()`__

To know if the request have result returned or not.

__`array public getPrepareDriversOptions()`__<br>
__`self public setPrepareDriversOptions(array $driverOptions)`__

Getter and setter to know/define options to use for prepared request of the PDO driver.
See the doc for the parameter `$driver_options` of the method [PDO::execute](http://php.net/manual/en/pdo.prepare.php).

__`\BfwSql\Queries\AbstractQuery public getQuery()`__

Getter to access to the `\BfwSql\Queries\AbstractQuery` object who have instantiate this class.

__`\BfwSql\SqlConnect public getSqlConnect()`__

Getter to access to the `\BfwSql\SqlConnect` who contain the PDO instance to use to execute the query.

__`\PDOStatement|int public execute()`__

Execute a query (via the method `executeQuery`), check errors, check number of line returned,
and return the value of the property `$lastRequestStatement`.

__`array protected executeQuery()`__

Prepare the system to execute a query, execute the query, and run post-executed methods.

In detail :
* Increment the number of request executed
* Call the method `AbstractQuery::assemble()` to assemble the final request
* Check if the system should use prepared request or not
* Call methods `executeNotPreparedQuery` or `executePreparedQuery` to execute the query
* Get errors infos for the request
* And call the method `callObservers`

__`\PDOStatement|int|bool protected executeNotPreparedQuery()`__

Execute a not prepared query with methods `PDO::exec` or `PDO::query`.

__`\PDOStatement|bool protected executePreparedQuery()`__

Execute a prepared query with the method `PDO::prepare`.

__`int|bool public obtainImpactedRows()`__

Obtain the number of impacted rows. If the property `$lastRequestStatement` is an int, we return this value;
but if it's a `PDOStatement` object, we use the method `PDOStatement::rowCount()` to obtain the number.

__`bool public closeCursor()`__

Call the method `PDOStatement::closeCursor()`.
If the property `$lastRequestStatement` not contain a `PDOStatement` object, an `Exception` will be throw.

__`void protected callObserver()`__

Call all observer defined with the request infos.

## Executers\Select

__`string public getReturnType()`__<br>
__`self public setReturnType(string $returnType)`__

To know/define the returned data type should have.
The possible values are `object` or `array`.

__`int protected obtainPdoFetchType()`__

Convert the value of the property `$returnType` to the value readed by `PDOStatement::fetch*` methods.

Conversion is:
* `object` → `PDO::FETCH_OBJ`
* `array` → `PDO::FETCH_ASSOC`

__`mixed public fetchRow(bool $reexecute=false)`__

Return the returned value of the `PDOStatement::fetch` method.
If the property `$lastRequestStatement` is `null`, or if the parameter `$reexecute` is true, the method `execute()` will be called before fetch.

__`\Generator public fetchAll()`__

Call the method `execute` and loop on method `PDOStatement::fetch`.
For each readed line, the generator is called (yield) with the value of the line.
