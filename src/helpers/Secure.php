<?php

namespace BfwSql\Helpers;

use \Exception;

/**
 * Helpers to securize data
 */
class Secure
{
    /**
     * Protect datas with sql protect method
     * 
     * @param string $datas Datas to protect
     * 
     * @return string
     * 
     * @throw \Exception If no database connected
     */
    public static function protectDatas($datas)
    {
        $app      = \BFW\Application::getInstance();
        $dbModule = $app->getModule('bfw-api');
        
        if (count($dbModule->listBases) === 0) {
            throw new Exception('No database connected to protect data');
        }
        
        return $dbModule->listBases[0]->protect($datas);
    }
}
