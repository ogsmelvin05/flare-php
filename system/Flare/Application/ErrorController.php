<?php

namespace Flare\Application;

use Flare\Application\AbstractController;

/**
 * 
 * @author anthony
 * 
 */
abstract class ErrorController extends AbstractController
{
    /**
     *
     * @var string
     */
    private $_errMessage;
    
    /**
     * 
     * @return void
     */
    public function init() {}

    /**
     * 
     * @return void
     */
    public function complete() {}
    
    /**
     * 
     * @param string $message
     * @return \Flare\Application\ErrorController
     */
    public function setErrorMessage($message)
    {
        $this->_errMessage = $message;
        return $this;
    }

    /**
     * 
     * @return string
     */
    public function getErrorMessage()
    {
        if ($this->_errMessage) {
            return $this->_errMessage;
        }
        return $this->response->getStatusMessage();
    }
    
    /**
     * 
     * @param int $code
     * @return \Flare\Application\ErrorController
     */
    public function setErrorCode($code)
    {
        $this->response->setStatusCode($code);
        return $this;
    }

    /**
     * 
     * @return int
     */
    public function getErrorCode()
    {
        return $this->response->getStatusCode();
    }
}