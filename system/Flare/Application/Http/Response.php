<?php

namespace Flare\Application\Http;

use Flare\Http\Response as ParentResponse;
use Flare\Flare as F;

/**
 * 
 * @author anthony
 * 
 */
class Response extends ParentResponse
{
    /**
     * 
     * @param string $name
     * @param string $value
     * @param int $expire
     * @param string path
     * @param string $domain
     * @param boolean $secure
     * @param boolean $httponly
     * @return \Flare\Application\Http\Response
     */
    public function addCookie($name, $value, $expire = 0, $path = '/', $domain = '', $secure = false, $httponly = false)
    {
        if (!$domain) {
            $domain = F::$uri->host;
        }
        setcookie($name, $value, $expire, $path, $domain, $secure, $httponly);
        return $this;
    }

    /**
     * 
     * @param array $cookies
     * @return \Flare\Application\Http\Response
     */
    public function addCookies(array $cookies)
    {
        foreach ($cookies as $cookie) {
            if (!isset($cookie['name'],
                $cookie['value'],
                $cookie['expire'],
                $cookie['path'],
                $cookie['domain'],
                $cookie['secure'],
                $cookie['httponly']))
            {
                $this->addCookie(
                    $cookie['name'],
                    $cookie['value'],
                    $cookie['expire'],
                    $cookie['path'],
                    $cookie['domain'],
                    $cookie['secure'],
                    $cookie['httponly']
                );
            } else {
                show_error('Incomplete cookie parameters');
            }
        }
        return $this;
    }
}