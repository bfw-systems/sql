<?php
/**
 * Config file for monolog
 * 
 * @author Vermeulen Maxime <bulton.fr@gmail.com>
 * @package bfw-sql
 * @version 2.0
 */

use Monolog\Logger;

return (object) [
    /**
     * List of monolog handlers.
     * It's a list of object. Each object contain the handler infos.
     * The object contain two keys
     * First is "name" (string), the name of the class.
     * Second is "args" (array), the list of argument passed to the constructor
     */
    'handlers' => [
        /**
         * Value example:
        (object) [
            'name' => '\Monolog\Handler\StreamHandler',
            'args' => [
                APP_DIR.'logs/bfw-sql/global.log',
                Logger::DEBUG
            ]
        ]
        */
    ]
];
