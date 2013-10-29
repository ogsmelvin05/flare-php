<?php

namespace Flare\Service;

use Flare\Http\Client\Curl\Request;
use Flare\Http\Client\Curl;
use Flare\Service;

/**
 * 
 * @author anthony
 * 
 */
class TextToSpeech extends Service
{
    /**
     * 
     * @var string
     */
    const API_HOST = 'http://tts-api.com/';

    /**
     * 
     * @param array $params
     * @return void
     */
    protected function init(array $params)
    {

    }

    /**
     * 
     * @param string $text
     * @return string
     */
    public function convert($text)
    {
        $request = new Request(self::API_HOST.'tts.mp3');
        $request->setParam('q', str_replace(array('"', "'"), '', $text))
                ->setParam('return_url', 1);

        $response = Curl::execute($request);
        if ($response->hasError()) {
            show_error($response->getError());
        }

        return $response->getBody();
    }
}