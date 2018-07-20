<?php
/**
 * Config file for bfw-sql module
 * 
 * @author Vermeulen Maxime <bulton.fr@gmail.com>
 * @package bfw-sql
 * @version 2.0
 */

return (object) [
    'observer' => (object) [
        'enable'  => true,
        'logFile' => 'app/logs/sql.log',
    ],
    'bases'    => [
        (object) [
            'baseKeyName' => 'travis',
            'filePath'    => '',
            'host'        => 'localhost',
            'port'        => 3306,
            'baseName'    => 'bfw_sql_tests',
            'user'        => 'travis',
            'password'    => '',
            'baseType'    => 'mysql',
            'pdoOptions'  => [],
            'useUtf8'     => true,
            'tablePrefix' => 'test_'
        ]
    ]
];
