# Queries\Parts

Like I said before, the queries generator system cut this queries into sevarals parts.
The majority of parts is an instance of a Parts classes.

A Part class is only responsible for the part of the request that corresponds to it.

## Existing parts

The list of all parts who can be used :

* `ColumnList` : A list of `Column` object without defined value. Used by SELECT queries for example
* `ColumnValueList` : A list of `Column` object with a defined value. Used by UPDATE queries for example
* `CommonList` : A list of expression
* `JoinList` : A list of `Join` object
* `Limit` : Used for LIMIT query part. Have the offset and the number of row properties
* `OrderList` : A list of `Order` object
* `SubQueryList` : A list of `SubQuery` object
* `Table` : Define a table datas (name and shortcut)
* `WhereList` : A list of expression

List of object can be defined by part list type :
* `Column` : Define a column : name, shortcut and value
* `Table` : Define a table : name and shortcut
* `Join` : Extends `Table` object, and add the "on" condition
* `Order` : An expression and this sort (ASC or DESC)
* `SubQuery` : The query and this shortcut

## How it works

A Part class should implement `\BfwSql\Queries\Parts\PartInterface`.
There is also the class `\BfwSql\Queries\Parts\AbstractPart`.
It add many properties who are used when the final request is generated.
And for a listing part, there is the class `\BfwSql\Queries\Parts\AbstractList` who implement `\Iterator`.

All used Parts classes is instantiate into the method `defineQueriesParts()` of Queries classes.
The user will access to the instance with the call of dynamic method into Queries classes (via the method `__call`).
When `__call` is called, it find the correct Part to use from the dynamic method name called,
and call the method `__invoke` of the Part class with passed arguments.

So the method `__invoke` is the main way used to set datas into the Part.

After that, during the call to the method `AbstractQuery::assemble`, each Part will generate this part of the final request.
For that, the method `generate()` will be used. Each part should implement it (because interface).
This method will generate the sql corresponding to this Part (without prefix like "WHERE") and return it.
