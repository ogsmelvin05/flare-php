<?php

namespace Flare\View;

/**
 * 
 * @author anthony
 * 
 */
abstract class Response
{
    /**
     * 
     * @var string
     */
    protected $contentType;

    /**
     * 
     * @return string
     */
    abstract public function render();

    /**
     * 
     * @return string
     */
    public function getContentType()
    {
        return $this->contentType;
    }

    /**
     * 
     * @return \Flare\View\Response
     */
    public function loadMiddleware()
    {
        require_once dirname(__FILE__).'/Middleware.php';
        return $this;
    }

    /**
     * 
     * @return string
     */
    public function __toString()
    {
        return $this->render();
    }
}