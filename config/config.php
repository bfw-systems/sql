<?php
/**
 * Config file for bfw-sql module
 * 
 * @author Vermeulen Maxime <bulton.fr@gmail.com>
 * @package bfw-sql
 * @version 2.0
 */

return (object) [
    /**
     * @var \stdClass observer Informations about the observer
     * This observer run an EXPLAIN request for each SELECT request executed
     */
    'observer' => (object) [
        /**
         * @var boolean enable If the observer is enabled
         */
        'enable' => false,
        
        /**
         * @var string path to the logfile from the root server
         */
        'logFile' => '',
    ],
    
    /**
     * @var \stdClass[] bases All database list to connect
     */
    'bases' => [
        (object) [
            /**
             * @var string baseKeyName Key used to identify this connection.
             * Because there are possibility to connect at multiple databases, 
             * we need a key to identify this base.
             * You can keep this value empty ONLY if there are one connection.
             */
            'baseKeyName' => '',
            
            /**
             * @var string host Datatable host to connect
             */
            'host'        => '',
            
            /**
             * @var string baseName Database name to connect
             */
            'baseName'    => '',
            
            /**
             * @var string user Database user used to connect
             */
            'user'        => '',
            
            /**
             * @var string password Database password used to connect
             */
            'password'    => '',
            
            /**
             * @var string baseType Database type used to connect
             *  It's the name of the PDO driver
             */
            'baseType'    => '',
            
            /**
             * @var boolean useUTF8 Force datas to be UTF-8
             *  Used for Mysql
             * 
             * @link http://dev.mysql.com/doc/refman/5.7/en/charset-connection.html
             */
            'useUTF8'     => true,
            
            /**
             * @var string tablePrefix The prefix used for all table.
             *  Empty if not prefix to use
             */
            'tablePrefix' => ''
        ]
        // Add object (duplicate first) into the array
        // to add others sql connexions
    ]
];
