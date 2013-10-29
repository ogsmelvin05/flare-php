<?php

namespace Flare\Http\Client;

use Flare\Http\Client\Curl\Response;
use Flare\Http\Client\Curl\Request;

if (!function_exists('curl_init')) {
    show_error('CURL is not supported by your server');
}

/**
 * 
 * @author anthony
 * 
 */
class Curl
{
    /**
     * 
     * @param \Flare\Http\Client\Curl\Request $request
     * @return \Flare\Http\Client\Curl\Response
     */
    public static function execute(Request $request)
    {
        $curl = curl_init();
        $options = $request->getOptions();

        switch ($request->getMethod()) {

            case Request::METHOD_POST:
                curl_setopt($curl, CURLOPT_POST, true);
                curl_setopt($curl, CURLOPT_POSTFIELDS, $request->getParams());
                break;
            
            case Request::METHOD_PUT:
                curl_setopt($curl, CURLOPT_PUT, true);
                break;

            case Request::METHOD_GET:
                if ($request->getParams()) {
                    $url = parse_url($options[CURLOPT_URL]);
                    if (!empty($url['query'])) {
                        parse_str($url['query'], $params);
                        $url['query'] = http_build_query(array_merge($params, $request->getParams()));
                    } else {
                        $url['query'] = http_build_query($request->getParams());
                    }
                    $options[CURLOPT_URL] = http_build_url($url);
                }
                curl_setopt($curl, CURLOPT_HTTPGET, true);
                break;

            default:
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $request->getMethod());
                break;
        }

        foreach ($options as $key => $option) {
            curl_setopt($curl, $key, $option);
        }

        if ($request->isHeaderOut()) {
            curl_setopt($curl, CURLINFO_HEADER_OUT, true);
        }

        $response = (string) curl_exec($curl);

        $errorMessage = null;
        if ($errorCode = curl_errno($curl)) {
            $errorMessage = curl_error($curl);
        }

        $body = null;
        $header = null;
        if ($response) {
            list($header, $body) = explode("\r\n\r\n", $response, 2);
        }
        $info = curl_getinfo($curl);
        curl_close($curl);
        
        return new Response($body, $info, $header, $errorCode, $errorMessage);
    }
}