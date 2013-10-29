<?php

namespace Flare\Http;

/**
 * 
 * @author anthony
 * 
 */
abstract class AbstractResponse
{
    /**
     * 
     * @var int
     */
    const DEFAULT_CODE = 200;

    /**
     * 
     * @var int
     */
    protected $_statusCode = self::DEFAULT_CODE;

    /**
     *
     * @var array
     */
    public static $messages = array(

        // Informational 1xx
        100 => 'Continue',
        101 => 'Switching Protocols',

        // Success 2xx
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',

        // Redirection 3xx
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',  // 1.1
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',

        // 306 is deprecated but reserved
        307 => 'Temporary Redirect',

        // Client Error 4xx
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Requested Range Not Satisfiable',
        417 => 'Expectation Failed',

        // Server Error 5xx
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
        509 => 'Bandwidth Limit Exceeded'
    );

    /**
     *
     * @param int $code
     * @return \Flare\Http\AbstractResponse
     */
    public function setStatusCode($code)
    {
        $this->_statusCode = (int) $code;
        return $this;
    }

    /**
     *
     * @return int
     */
    public function getStatusCode()
    {
        return $this->_statusCode;
    }

    /**
     * 
     * @param int $code
     * @return string
     */
    public function getStatusMessage($code = null)
    {
        if (!$code) {
            $code = $this->_statusCode;
        }
        return isset(self::$messages[$code]) ? self::$messages[$code] : '';
    }
    
    /**
     * 
     * @return boolean
     */
    public function isNotFound()
    {
        return ($this->_statusCode === 404);
    }

    /**
     * 
     * @return boolean
     */
    public function isOk()
    {
        return ($this->_statusCode === self::DEFAULT_CODE);
    }

    /**
     * 
     * @return boolean
     */
    public function isServerError()
    {
        return (500 <= $this->_statusCode && 600 > $this->_statusCode);
    }

    /**
     *
     * @return boolean
     */
    public function isForbidden()
    {
        return ($this->_statusCode === 403);
    }

    /**
     * 
     * @return boolean
     */
    public function isRedirect()
    {
        return (300 <= $this->_statusCode && 400 > $this->_statusCode);
    }

    /**
     * 
     * @return boolean
     */
    public function isSuccess()
    {
        return (200 <= $this->_statusCode && 300 > $this->_statusCode);
    }

    /**
     * 
     * @return boolean
     */
    public function isInformational()
    {
        return ($this->_statusCode >= 100 && $this->_statusCode < 200);
    }

    /**
     * 
     * @return boolean
     */
    public function isClientError()
    {
        return ($this->_statusCode < 500 && $this->_statusCode >= 400);
    }
}