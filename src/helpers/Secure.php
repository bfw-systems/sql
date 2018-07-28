<?php

namespace BfwSql\Helpers;

use \Exception;

/**
 * Helpers to securize data
 */
class Secure
{
    const ERR_NO_DATABASE_CONNECTED = 2201001;
    
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
        $dbModule = \BFW\Application::getInstance()
            ->getModuleList()
            ->getModuleForName('bfw-sql')
        ;
        
        if (count($dbModule->listBases) === 0) {
            throw new Exception(
                'No database connected to protect data',
                self::ERR_NO_DATABASE_CONNECTED
            );
        }
        
        return reset($dbModule->listBases)->protect($datas);
    }
}
