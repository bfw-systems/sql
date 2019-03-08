<?php
/**
 * Config file for bfw-sql module
 * 
 * @author Vermeulen Maxime <bulton.fr@gmail.com>
 * @package bfw-sql
 * @version 2.0
 */

return [
    'bases' => [
        new class {
            public $baseKeyName   = 'travis';
            public $filePath      = '';
            public $host          = 'localhost';
            public $port          = 3306;
            public $baseName      = 'bfw_sql_tests';
            public $user          = 'travis';
            public $password      = '';
            public $baseType      = 'mysql';
            public $encoding      = 'utf8';
            public $tablePrefix   = 'test_';
            public $pdoOptions    = [];
            public $pdoAttributes = [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION
            ];
        }
    ]
];
