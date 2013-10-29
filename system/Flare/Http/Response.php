<?php

namespace Flare\Http;

use Flare\Http\AbstractResponse;
use Flare\Flare as F;

/**
 *
 * @author anthony
 *
 */
class Response extends AbstractResponse
{
    /**
     *
     * @var array
     */
    protected $_headers = array();

    /**
     *
     * @var string
     */
    protected $_body;

    /**
     * 
     * @var string
     */
    protected $_redirectUrl;

    /**
     * 
     * @var string
     */
    protected $_contentType;

    /**
     * 
     * @var boolean
     */
    protected $_sent = false;

    /**
     *
     * @param string $header
     * @param boolean $replace
     * @return \Flare\Http\Response
     */
    public function setHeader($header, $replace = true)
    {
        $this->_headers[] = array(
            'header' => $header,
            'replace' => $replace
        );
        return $this;
    }

    /**
     *
     * @param string $type
     * @return \Flare\Http\Response
     */
    public function setContentType($type)
    {
        $this->_contentType = $type;
        return $this->setHeader('Content-Type: '.$type);
    }

    /**
     * 
     * @return string
     */
    public function getContentType()
    {
        if (!$this->_contentType) {
            foreach (headers_list() as $header) {
                $header = strtolower($header);
                if (strpos($header, 'content-type') === 0) {
                    return trim(str_replace('content-type: ', '', $header));
                }
            }
            return null;
        }
        return $this->_contentType;
    }

    /**
     * 
     * @return boolean
     */
    public function hasContentType()
    {
        return $this->getContentType() ? true : false;
    }

    /**
     *
     * @param string $view
     * @return \Flare\Http\Response
     */
    public function setBody($view)
    {
        $this->_body = (string) $view;
        return $this;
    }

    /**
     *
     * @param string $url
     * @param int $code
     * @return \Flare\Http\Response
     */
    public function setRedirect($url, $code = 302)
    {
        $this->_redirectUrl = $url;
        return $this->setStatusCode($code);
    }

    /**
     * 
     * @param int $seconds
     * @param string $url
     * @return void
     */
    public function setRefresh($seconds = 0, $url = null)
    {
        if (is_string($seconds)) {
            $seconds = (int) $seconds;
        }
        if (!$url) {
            $url = F::$uri->current;
        } elseif (parse_url($url, PHP_URL_SCHEME) === null) {
            $url = F::$uri->base.ltrim($url, '/');
        }
        $this->setHeader("Refresh: {$seconds};url=\"{$url}\"");
    }

    /**
     * 
     * @param string $path
     * @param string $filename
     * @return void
     */
    public function download($path, $filename = null)
    {
        
    }

    /**
     * 
     * @param boolean $output_body
     * @return void
     */
    public function send($output_body = true)
    {
        if ($this->_sent) {
            show_response(500, "Response::send already executed");
        }
        if ($this->_statusCode !== self::DEFAULT_CODE && isset(self::$messages[$this->_statusCode])) {
            if (!empty($_SERVER['SERVER_PROTOCOL'])) {
                header($_SERVER['SERVER_PROTOCOL'].' '.$this->_statusCode.' '.self::$messages[$this->_statusCode]);
            } else {
                header('HTTP/1.1 '.$this->_statusCode.' '.self::$messages[$this->_statusCode]);
            }
        }
        if (!empty($this->_redirectUrl) && (300 <= $this->_statusCode) && (307 >= $this->_statusCode)) {
            header('Location: '.$this->_redirectUrl);
            exit;
        }

        foreach ($this->_headers as $header) {
            header($header['header'], $header['replace']);
        }

        if ($output_body) {
            echo $this->_body;
        }
        $this->_sent = true;
    }
}

