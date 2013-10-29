<?php

namespace Flare\Service\GitHub;

use Flare\Http\Client\Curl\Request;
use Flare\Service\GitHub\Object;
use Flare\Http\Client\Curl;
use Flare\Service\GitHub;
use Flare\Object\Json;

/**
 * 
 * @author anthony
 * 
 */
class User extends Object
{
    /**
     * 
     * @var string
     */
    private $username;

    /**
     * 
     * @var string
     */
    private $password;

    /**
     * 
     * @param \Flare\Service\GitHub $git
     * @param string $username
     * @param string $password
     */
    public function __construct(GitHub $git, $username, $password = null)
    {
        parent::__construct($git);
        $this->username = urlencode($username);
        if ($password) {
            $this->setPassword($password);
        }
    }

    /**
     * 
     * @param string $password
     * @return \Flare\Service\GitHub\User
     */
    public function setPassword($password)
    {
        $this->password = $password;
        return $this;
    }

    /**
     * 
     * @return string
     */
    public function getName()
    {
        return $this->username;
    }

    /**
     * 
     * @param array $attributes
     * @return void
     */
    protected function setAttributes(array $attributes = array())
    {
        $request = new Request(GitHub::HOST.'users/'.$this->username);
        $request->setOption('ssl_verifypeer', 1)
            ->setOption('ssl_verifyhost', 2)
            ->setOption('cainfo', $this->git->getCertificatePath());
        if ($this->password) {
            $request->setOption('userpwd', $this->username.':'.$this->password);
        }
        $response = Curl::execute($request);

        if ($response->hasError()) {
            show_error($response->getError());
        }

        $this->attributes = $response->getBodyAsJson();
    }

    /**
     * 
     * @param string $key
     * @return mixed
     */
    public function getAttribute($key)
    {
        if (!$this->attributes) {
            $this->setAttributes();
        }

        if (!isset($this->attributes[$key])) {
            show_error("Undefined '{$key}' attribute");
        }
        return $this->attributes[$key];
    }

    /**
     * 
     * @return \Flare\Object\Json
     */
    public function getAttributes()
    {
        if (!$this->attributes) {
            $this->setAttributes();
        }
        return $this->attributes;
    }

    /**
     * 
     * @return \Flare\Object\Json
     */
    public function getRepositories()
    {
        $request = new Request(GitHub::HOST.'users/'.$this->username.'/repos');
        $request->setOption('ssl_verifypeer', 1)
            ->setOption('ssl_verifyhost', 2)
            ->setOption('cainfo', $this->git->getCertificatePath());
        if ($this->password) {
            $request->setOption('userpwd', $this->username.':'.$this->password);
        }
        $response = Curl::execute($request);
        
        if ($response->hasError()) {
            show_error($response->getError());
        }
        
        return $response->getBodyAsJson();
    }
}