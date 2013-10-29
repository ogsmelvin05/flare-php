<?php

namespace Flare\Db;

/**
 * 
 * @author anthony
 * 
 */
interface Connection
{
    /**
     * 
     * @param string $name
     * @param array $options
     * @return \PDO
     */
    public static function create($name, array $options);

    /**
     * 
     * @param string $name
     * @return void
     */
    public static function destroy($name = null);
}