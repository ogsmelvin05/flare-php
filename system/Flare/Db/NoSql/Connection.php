<?php

namespace Flare\Db\NoSql;

use Flare\Db\Connection as ParentConnection;

/**
 * 
 * @author anthony
 * 
 */
class Connection implements ParentConnection
{
    /**
     * 
     * @param string $name
     * @param array $options
     * @return \Flare\Db\NoSql\Driver
     */
    public static function create($name, array $options)
    {
        
    }

    /**
     * 
     * @param string $name
     * @return void
     */
    public static function destroy($name = null)
    {

    }
}