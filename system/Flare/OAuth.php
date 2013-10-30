<?php

namespace Flare;

use Flare\OAuth\Client;
use Flare\OAuth\Server;

/**
 * 
 * @author anthony
 * 
 */
class OAuth
{
    /**
     * 
     * @var string
     */
    const VERSION = '2.0';

    /**
     * 
     * @return \Flare\OAuth\Client
     */
    public static function client()
    {
        return new Client();
    }

    /**
     * 
     * @return \Flare\OAuth\Server
     */
    public static function server()
    {
        return new Server();
    }
}