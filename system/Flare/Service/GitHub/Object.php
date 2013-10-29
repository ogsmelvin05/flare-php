<?php

namespace Flare\Service\GitHub;

use Flare\Service\GitHub;

/**
 * 
 * @author anthony
 * 
 */
abstract class Object
{
    /**
     * 
     * @var \Flare\Service\GitHub
     */
    protected $git;

    /**
     * 
     * @var \Flare\Object\Json
     */
    protected $attributes;

    /**
     * 
     * @param \Flare\Service\GitHub $github
     */
    public function __construct(GitHub $github)
    {
        $this->git = $github;
    }

    /**
     * 
     * @return \Flare\Object\Json
     */
    abstract public function getAttributes();

    /**
     * 
     * @param array $attributes
     * @return void
     */
    abstract protected function setAttributes(array $attributes = array());

    /**
     * 
     * @param string $key
     * @return mixed
     */
    abstract public function getAttribute($key);
}