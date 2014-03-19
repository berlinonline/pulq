<?php

namespace Pulq\Agavi\Database;

use \AgaviDatabaseManager;

class PulqDatabaseManager extends AgaviDatabaseManager
{
    public function getDatabaseNames()
    {
        $db_names = array_values(
            array_map(function($d){
                return $d->getName();
            }, $this->databases)
        );

        return $db_names;
    }
}

