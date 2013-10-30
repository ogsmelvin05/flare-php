<?php

namespace Flare\OAuth;

use Flare\OAuth;

/**
 * 
 * @author anthony
 * 
 */
class Client
{
    /**
     * 
     * @var string
     */
    private $id;

    /**
     * 
     * @var string
     */
    private $secret;

    /**
     * 
     * @var string
     */
    private $redirectUri;

    /**
     * 
     * @param string $id
     * @return \Flare\OAuth\Client
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * 
     * @param string $secret
     * @return \Flare\OAuth\Client
     */
    public function setSecret($secret)
    {
        $this->secret = $secret;
        return $this;
    }

    /**
     * 
     * @param string $uri
     * @return \Flare\OAuth\Client
     */
    public function setRedirectUri($uri)
    {
        $this->redirectUri = $uri;
        return $this;
    }

    /**
     * 
     * @return string
     */
    public function getRedirectUri()
    {
        return $this->redirectUri;
    }

    /**
     * 
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * 
     * @return string
     */
    public function getSecret()
    {
        return $this->secret;
    }
}