<?php

namespace Flare\Db\Sql;

use Flare\Db\Connection as ParentConnection;
use PDO;

/**
 * 
 * @author anthony
 * 
 */
class Connection implements ParentConnection
{
    /**
     * 
     * @var array
     */
    private static $_connections = array();

    /**
     * 
     * @param string $name
     * @param array $config
     * @return \Flare\Db\Sql\Driver
     */
    public static function create($name, array $config)
    {
        if (!isset(self::$_connections[$name])) {
            if (!$config) {
                if (!isset(F::$config->database[$name])) {
                    show_error("No database configuration found");
                }
                $config = F::$config->database[$name];
            }
            if (isset($config['driver'], $config['host'], $config['dbname'], $config['username'], $config['password'])) {
                $config['driver'] = strtolower($config['driver']);
                $dns = $config['driver'].':host='.$config['host'].';dbname='.$config['dbname'];
                $pdo = "\\Flare\\Db\\Sql\\Driver\\".ucwords($config['driver']);
                if (!isset($config['options'])) {
                    $config['options'] = array();
                }
                self::$_connections[$name] = new $pdo($dns, $config['username'], $config['password'], $config['options']);
            }
        }
        return isset(self::$_connections[$name]) ? self::$_connections[$name] : show_error("Can't create database connection");
    }

    /**
     * 
     * @param string $name
     * @return void
     */
    public static function destroy($name = null)
    {
        if (!$name) {
            foreach (self::$_connections as &$conn) {
                if (!$conn->getAttribute(PDO::ATTR_PERSISTENT)) $conn = null;
            }
        } elseif (isset(self::$_connections[$name]) 
            && !self::$_connections[$name]->getAttribute(PDO::ATTR_PERSISTENT))
        {
            self::$_connections[$name] = null;
        }
    }
}