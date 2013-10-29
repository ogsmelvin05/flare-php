<?php

namespace Flare\Service;

use Flare\Service\GitHub\User;
use Flare\Service\Certificate;
use Flare\Service;

/**
 * 
 * @author anthony
 * 
 */
class GitHub extends Service implements Certificate
{
    /**
     * 
     * @var string
     */
    const HOST = 'https://api.github.com/';

    /**
     * 
     * @var string
     */
    private $cert;

    /**
     * 
     * @param array $params
     * @return void
     */
    public function init(array $params)
    {
        $this->cert = dirname(__FILE__).'/GitHub/ssl.crt';
    }

    /**
     * 
     * @return string
     */
    public function getCertificatePath()
    {
        return $this->cert;
    }

    /**
     * 
     * @param string $name
     * @param string $password
     * @return \Flare\Service\GitHub\User
     */
    public function getUser($username, $password = null)
    {
        return new User($this, $username, $password);
    }
}