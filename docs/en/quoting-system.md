# Quoting System

Requests like INSERT or UPDATE need to have string values quoted.
So for this requests, you need to add quotes around your values when you write the request.
That means when you use generated queries system, you need to add quote into your value. It's not great.

To avoid that, an automatic quoting system has been added.
You declare the default mode (quote or not), and column to quote (or not), and the system will quote values for you.

## Use it

The system is a helper, the class name is `\BfwSql\Helpers\Quoting`.

__`self public __construct(string $status, \BfwSql\SqlConnect $sqlConnect)`__

`$status` is the global action to use when the system will quote (or not) a value.
Three values can be used :
* `Quoting::QUOTE_ALL` : All values will be quoted
* `Quoting::QUOTE_NONE` : No values will be quoted
* `Quoting::QUOTE_PARTIALLY` : You define values to quote and values to not quote.

The `$sqlConnect` parameter is to have an opened PDO connection to use for call the method `PDO::quote()` used to quote values.

__`self public setPartiallyPreferedMode(string $partiallyPreferedMode)`__

If you use the `QUOTE_PARTIALLY` status, you should define the prefered mode to use.
Two values can be used :
* `Quoting::PARTIALLY_MODE_QUOTE` : If the column is not declared quoted or not quoted, the value will be quoted.
* `Quoting::PARTIALLY_MODE_NOTQUOTE` : If the column is not declared quoted or not quoted, the value will not be quoted.

You have a table into the [issue #41](https://github.com/bulton-fr/bfw-sql/issues/41#issuecomment-389154260)
to understand how the partially mode will react.

__`self public addQuotedColumns(string[] $columns)`__<br>
__`self public addNotQuotedColumns(string[] $columns)`__

If you use the "partially" mode, you can define what values will be quoted (or not) with their columns name.
With methods `addQuotedColumns` and `addNotQuotedColumns` you can define a list of columns name to quote (or not).

__`mixed public quoteValue(string $columnName, mixed $value)`__

It's the method will quote (or not) the value.
It use the declared status (and prefered mode if partially) to determine if the value should be quoted or not.
The value to use is returned by the method.

Note : Only string value is quoted. If you declared a column to be quoted but the value is, for example, an integer, the value will not be quoted.

## Example

```sql
CREATE TABLE `quoting_example` (
    `iduser` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `login` varchar(255) NOT NULL,
    `password` varchar(255) NOT NULL,
    `mail` varchar(255) NOT NULL,
    `idaccess` int(10) UNSIGNED NOT NULL,
    PRIMARY KEY (`iduser`)
);
```

### QUOTE_ALL
```php
use \BfwSql\Helpers\Quoting;

$datas = [
    'login'    => 'bulton-fr',
    'password' => 'not-real-passwd',
    'mail'     => 'my@email.com',
    'idaccess' => 1
];

$quoting = new Quoting(Quoting::QUOTE_ALL, $sqlConnect);

$quotedValues = [];
foreach ($datas as $columnName => $columnValue) {
    $quotedValues[$columnName] = $quoting->quoteValue($columnName, $columnValue);
}

var_dump($quotedValues);
/*
array(4) {
  ["login"]=> string(11) "'bulton-fr'"
  ["password"]=> string(17) "'not-real-passwd'"
  ["mail"]=> string(14) "'my@email.com'"
  ["idaccess"]=> int(1)
}
*/
```

### QUOTE_NONE
```php
use \BfwSql\Helpers\Quoting;

$datas = [
    'login'    => 'bulton-fr',
    'password' => 'not-real-passwd',
    'mail'     => 'my@email.com',
    'idaccess' => 1
];

$quoting = new Quoting(Quoting::QUOTE_ALL, $sqlConnect);

$quotedValues = [];
foreach ($datas as $columnName => $columnValue) {
    $quotedValues[$columnName] = $quoting->quoteValue($columnName, $columnValue);
}

var_dump($quotedValues);
/*
array(4) {
  ["login"]=> string(9) "bulton-fr"
  ["password"]=> string(15) "not-real-passwd"
  ["mail"]=> string(12) "my@email.com"
  ["idaccess"]=> int(1)
}
*/
```

### QUOTE_PARTIALLY

#### Without prefered mode declared

```php
use \BfwSql\Helpers\Quoting;

$datas = [
    'login'    => 'bulton-fr',
    'password' => 'not-real-passwd',
    'mail'     => 'my@email.com',
    'idaccess' => 1
];

$quoting = new Quoting(Quoting::QUOTE_PARTIALLY, $sqlConnect);

$quotedValues = [];
foreach ($datas as $columnName => $columnValue) {
    $quotedValues[$columnName] = $quoting->quoteValue($columnName, $columnValue);
}

var_dump($quotedValues);
/*
array(4) {
  ["login"]=> string(11) "'bulton-fr'"
  ["password"]=> string(17) "'not-real-passwd'"
  ["mail"]=> string(14) "'my@email.com'"
  ["idaccess"]=> int(1)
}
*/
```

#### With prefered mode to not quoted and column declared to quote

```php
use \BfwSql\Helpers\Quoting;

$datas = [
    'login'    => 'bulton-fr',
    'password' => 'not-real-passwd',
    'mail'     => 'my@email.com',
    'idaccess' => 1
];

$quoting = new Quoting(Quoting::QUOTE_PARTIALLY, $sqlConnect);
$quoting
    ->setPartiallyPreferedMode(Quoting::PARTIALLY_MODE_NOTQUOTE)
    ->addQuotedColumns(['login', 'mail'])
;

$quotedValues = [];
foreach ($datas as $columnName => $columnValue) {
    $quotedValues[$columnName] = $quoting->quoteValue($columnName, $columnValue);
}

var_dump($quotedValues);
/*
array(4) {
  ["login"]=> string(11) "'bulton-fr'"
  ["password"]=> string(15) "not-real-passwd"
  ["mail"]=> string(14) "'my@email.com'"
  ["idaccess"]=> int(1)
}
*/
```

## Integration into generated queries system

The generated queries system integrate the quoting system for some queries type.
Only `Queries\Insert` and `Queries\Update` integrate an instance of the Quoting class.
You can access it with the getter `getQuoting()` from the `Queries` instance.

### Insert

Into you model, you have this method `\BfwSql\Queries\Insert public insert([string $quoteStatus=\BfwSql\Helpers\Quoting::QUOTE_ALL])`.
It instantiate an object of the class `Queries\Insert`.

You can choose the quoting status to use (the value passed to the Quoting constructor). By default the status use the status `QUOTE_ALL`.

### Update

Into you model, you have this method `\BfwSql\Queries\Update public update([string $quoteStatus=\BfwSql\Helpers\Quoting::QUOTE_ALL])`.
It instantiate an object of the class `Queries\Update`.

You can choose the quoting status to use (the value passed to the Quoting constructor). By default the status use the status `QUOTE_ALL`.

### Example

Yes the column list to quote is wtf, if you have a better example (with the partially case), you are free to propose ;)

```php
namespace Models;

use \BfwSql\Helpers\Quoting;

class Users extends \BfwSql\AbstractModels
{
    protected $tableName = 'users';

    public function addUser($datas)
    {
        /*
        $datas = [
            'login'    => 'bulton-fr',
            'password' => 'not-real-passwd',
            'mail'     => 'my@email.com',
            'idaccess' => 1
        ];
        */

        $req = $this->insert(Quoting::QUOTE_PARTIALLY)
            ->into($this->tableName, $datas)
        ;

        $quote = $req->getQuoting()
            ->setPartiallyPreferedMode(Quoting::PARTIALLY_MODE_NOTQUOTE)
            ->addQuotedColumns(['login'])
        ;

        var_dump($req->assemble());
        /*
        INSERT INTO `test_users`
        (`login`,`password`,`mail`,`idaccess`) VALUES ('bulton-fr',not-real-passwd,my@email.com,1)
        */
        
        return $req->execute();
    }

    public function updateUser($datas)
    {
        /*
        $datas = [
            'login'    => 'bulton-fr',
            'password' => 'not-real-passwd',
            'mail'     => 'my@email.com',
            'idaccess' => 1
        ];
        */

        $req = $this->update(Quoting::QUOTE_PARTIALLY)
            ->from($this->tableName, $datas)
        ;

        $quote = $req->getQuoting()
            ->setPartiallyPreferedMode(Quoting::PARTIALLY_MODE_NOTQUOTE)
            ->addQuotedColumns(['login'])
        ;

        var_dump($req->assemble());
        /*
        UPDATE `test_users` SET
        `test_users`.`login`='bulton-fr',
        `test_users`.`password`=not-real-passwd,
        `test_users`.`mail`=my@email.com,
        `test_users`.`idaccess`=1
        */
        
        return $req->execute();
    }
}
```
