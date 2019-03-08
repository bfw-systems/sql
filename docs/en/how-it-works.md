# How it works ?

It's magic, like unicorn :unicorn:

## When the module start
More seriously, all modules start this the `runner.php` file. This file instantiate all runners classes :
* Monolog : Create the Monolog logger and attach handlers declared into `monolog.php` config file
* Observers : Create the subject `bfw-sql`, instantiate all observers declared into `observers.php` and create monolog logger defined for each of him
* ConnectDB : Create the connection to all database declared (instance an object of `\BfwSql\SqlConnect` for each connection)

So at this moment, all connection is open, monolog and observers is ready :)

## A model
Now, the next is into your Models and I will consider you use the class `\BfwSql\AbstractModels`.

So this abstract class use the property `$baseKeyName` to search and obtain the correct PDO instance to use
via the correct instance of the class `\BfwSql\SqlConnect` and send this instance to its parent, the class `\BfwSql\Sql`.

Ok so you have a model who know the connection to use, it's great, but now, the model will doing sql query.
For that, two choice :
* Use the method `query()` and write the query yourself.
* Use the system who generate query

### When you write the query

Ok you have use the method `query()` into your model and you have write the query yourself. What happen next ?
The method get the PDO instance and pass your query to the method `PDO::query()`.
Next, a notify is send to all observers.
And at the end, you get the return of the method `PDO::query()`, or an Exception is throw if there is an error.

### When you use the generated query system

This system can generate for you the majority of SELECT, INSERT, UPDATE and DELETE queries you will use.

For the example, we consider you use the method `select()`.
First this method instantiate the class `BfwSql\Queries\Select` and return you the instance.

#### Query parts

When this class is instantiate, many others is instantiate too. Others classes is each part of the query generated.

For example, a generated select query have 9 parts :
* subQuery : A list of all subqueries will be into the query (only into the column list)
* from : Datas about the main table (name and alias), and the list of columns from this table to return
* join : A list of item who is like from but for the join part, so there is the link ("on" part) into table datas
* joinLeft : Like join but for joinLeft part
* joinRight : Like join but for joinRight part
* where : A list of expression to use into the where part of the query
* order : A list of expression to use into the order by part of the query
* limit : The offset and number of row to use into the limit part of the query
* group : A list of expression to use into the order by part of the query

Each item have this own instance class (classes into namespace `\BfwSql\Queries\Parts`).
And each class know how to generate this own part of the query.

#### Executers

##### With Select

If you have generated a select query, now you want to obtain datas from your select query.
For that, you will get the executers class. Each queries class instantiate an executers class to execute this query.
To obtain it, you can use the getter `getExecuter()`.

Now you have the executer, and we always want datas. For that, there two methods : 
* `fetchAll` : Return a generator (with yield) to obtain all datas
* `fetchRow` : Return the current data line

For both, the executer will call this own method `execute()` if the query has not been executed.

##### With others queries types

For others queries type (insert, update and delete), you have one method to call to execute your query : `execute()`.
You can call this method from the Query class, not need to obtain the executers instance ;)

##### End after ?

After have executed the query with the method `execute` into the Executers, the method `callObserver` will be called.
This method will sent to a notify to all observer with a clone of the current instance of the Executer and Queries.
