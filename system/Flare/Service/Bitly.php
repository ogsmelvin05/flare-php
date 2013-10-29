<?php

namespace Flare\Service;

use Flare\Http\Client\Curl\Request;
use Flare\Http\Client\Curl;
use Flare\Service;

/**
 * 
 * @author
 * 
 */
class Bitly extends Service
{
    /**
     * 
     * @var string
     */
    private $_username;

    /**
     * 
     * @var string
     */
    private $_password;

    /**
     * 
     * @var string
     */
    const API_HOST = 'http://api.bit.ly/v3/';

    /**
     * 
     * @access protected
     * @param array $params
     * @return void
     */
    protected function init(array $params)
    {
        if (!isset($params['username'], $params['password'])) {
            show_error('Username and password must be set for Bitly Service');
        }
        $this->_username = $params['username'];
        $this->_password = $params['password'];
    }

    /**
     * 
     * @return string
     */
    public function getUsername()
    {
        return $this->_username;
    }

    /**
     * 
     * @param string $link
     * @param string $format
     * @return string
     */
    public function shorten($link, $format = 'txt')
    {
        $request = new Request(self::API_HOST.'shorten');
        $request->setParam('login', $this->_username)
            ->setParam('apiKey', $this->_password)
            ->setParam('uri', $link)
            ->setParam('format', $format);

        $response = Curl::execute($request);
        if ($response->hasError()) {
            show_error($response->getError());
        }

        return $response->getBody();
    }
}