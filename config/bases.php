<?php
/**
 * Config file for bfw-sql module
 * 
 * @author Vermeulen Maxime <bulton.fr@gmail.com>
 * @package bfw-sql
 * @version 2.0
 */

return [
    /**
     * @var array bases All database list to connect
     */
    'bases' => [
        new class {
            /**
             * @var string baseKeyName Key used to identify this connection.
             * Because there are possibility to connect at multiple databases, 
             * we need a key to identify this base.
             * You can keep this value empty ONLY if there are one connection.
             */
            public $baseKeyName = '';
            
            /**
             * @var string filePath Path to the file used for db
             * Used by sqlite driver for example. Keep empty if not used.
             */
            public $filePath = '';
            
            /**
             * @var string host Datatable host to connect
             */
            public $host = '';
            
            /**
             * @var int port Datatable port to connect
             * Mysql default is 3306
             */
            public $port = 0;
            
            /**
             * @var string baseName Database name to connect
             */
            public $baseName = '';
            
            /**
             * @var string user Database user used to connect
             */
            public $user = '';
            
            /**
             * @var string password Database password used to connect
             */
            public $password = '';
            
            /**
             * @var string baseType Database type used to connect
             *  It's the name of the PDO driver
             */
            public $baseType = '';
            
            /**
             * @var string encoding Define the encoding to use for requests.
             * Used with the query SET NAMES.
             * 
             * @link http://dev.mysql.com/doc/refman/5.7/en/charset-connection.html
             */
            public $encoding = '';
            
            /**
             * @var string tablePrefix The prefix used for all table.
             *  Empty if not prefix to use
             */
            public $tablePrefix = '';
            
            /**
             * @var array pdoOptions Options passed to 4th arguments
             * of PDO::__construct
             * 
             * @link http://php.net/manual/en/pdo.construct.php
             */
            public $pdoOptions = [];
            
            /**
             * @var array pdoAttributes Some attributes define with setAttribute
             * method.
             * 
             * @link http://php.net/manual/en/pdo.setattribute.php
             */
            public $pdoAttributes = [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION
            ];
        }
        // Add object (duplicate first) into the array
        // to add others sql connexions
    ]
];
