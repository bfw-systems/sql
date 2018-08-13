<?php
/**
 * Config file for monolog
 * 
 * @author Vermeulen Maxime <bulton.fr@gmail.com>
 * @package bfw-sql
 * @version 2.0
 */

use Monolog\Logger;

return [
    /**
     * List of observers to use
     * The key is the observer class, the value an object.
     * The object contain a key "monologHandlers" who are an object too.
     * This new object contain two key.
     * First is "use-global" (boolean), is the observer should use handlers
     *  declared into "monolog-global-handlers" 
     * Second is "others" (array), a list of handlers to use only for this
     *  observer. Format is the same of "handlers" in "monolog.php" config file
     */
    'observers' => [
        /**
         * Value example:
        [
            'className'       => '\BfwSql\Observers\Basic',
            'monologHandlers' => [
                'useGlobal' => true,
                'others'    => []
            ]
        ]
        */
    ]
];
