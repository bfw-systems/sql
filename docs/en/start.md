# Install

You can use composer to get the module : `composer require bulton-fr/bfw-sql @stable`

And to install the module : `./vendor/bin/bfwInstallModules`

# Configure

## bases.php

This file contains the list of all databases to connect.
Each base declared should be an object (you can use anonymous class for that).
The config file already contain a base structure to use.

The object should contains properties :
* `$baseKeyName` : The internal name for this connection. Used by modeles.
* `$filePath` : For SGBD like sqlite who use a file, the path to the database file
* `$host` : For SGBD like mysql who use a server, the host of the server
* `$port` : For SGBD like mysql who use a server, the port of the server
* `$baseName` : The basename into the server
* `$user` : The username to open the database
* `$password` : The user password
* `$baseType` : The type of SGBD (mysql, pgsql, sqlite, etc)
* `$encoding` : The database encoding, will be used in a `SET NAMES` query if not empty
* `$tablePrefix` : The prefix of all tables (empty if no prefix)
* `$pdoOptions` : Option to sent to PDO construct
* `$pdoAttributes` : An array of options which will be pass to PDO `setAttribute` method

## class.php

Used for dev who want extends class(es) of the module.
Some classes if instantiate by other, who are not always easier to access for be override too.
So this config file list all classes who are not easier to access to make their override easier.

This config file contain an array, for each key you have a class name.
When the system want instantiate a class, it call the class UserClass and ask the class who match the asked key.
So you should not modify key values !

## manifest.json

This file is used by BFW module upgrade system. You should not modify it.

## monolog.php

The framework and this module use [Monolog](https://github.com/Seldaek/monolog), and monolog use handlers.
This file contain the list of all handlers used by the module.
The v1.x version of monolog sent message to stdout if no handler is declared, so I declare the TestHandler by default.
You can disable it if you use an other handler ;)

The format for each handler declared is an array with two keys :
* `name` : The class name of the handler (with namespace)
* `args` : All arguments will be pass to handler construct.

## observer.php

The module integrate a subject system (design pattern Observer) who is called when a request is executed. Two observers is intergrate too.

With this config file, you can configure all observers will be attached to the subject.
For each observer, you can configure a dedicated monolog logger instance.

Each observers configured should be an array with two keys :
* `className` : The class name (with namespace) of the observer to attach
* `monologHandlers` : An array for configure handlers to use for this observer
  * `useGlobal` : If the handlers defined into the config file `monolog.php` will be used for this observer
  * `others` : All others handler to use for this observer.
The format is identical of the format used into the config file `monolog.php`

# The first modele

You can use the directory `/src/modeles` to save you modeles.
You can also use the namespace `\Modeles` for your modeles, this namespace is declared to composer and linked to the directory `/src/modeles` by the framework.

Your modeles can extends of the class `\BfwSql\AbstractModeles`.
This class extends of `\BfwSql\Sql` who gives you an access to generated queries system.

The class AbstractModeles have the property `$baseKeyName` which must correspond to the key `$baseKeyName` declared into the config file `bases.php`.
With that, the system will find the correct instance of `\BfwSql\SqlConnect` (who instantiate `PDO`) to execute queries on the correct database.

If you have only one database declared, you can not declared `$baseKeyName`, the system will automatically find the database (because there is only one, it's easy to know which one to take).

## Example

```php
namespace Modeles;

class MyTable extends \BfwSql\AbstractModeles
{
    protected $baseKeyName = 'testDb';
    
    protected $tableName = 'runner';
    
    public function obtainAll(): \Generator
    {
        $query = $this->select()->from($this->tableName, '*');
        
        return $query->getExecuter()->fetchAll();
    }
}
```
