<?php

namespace Flare\Service;

use Flare\Http\Client\Curl\Request;
use Flare\Service\Instagram\Tag;
use Flare\Service\Certificate;
use Flare\Http\Client\Curl;
use Flare\Service;

/**
 * 
 * @author anthony
 * 
 */
class Instagram extends Service implements Certificate
{
    /**
     * 
     * @var string
     */
    const HOST = 'https://api.instagram.com/v1/';

    /**
     * 
     * @var string
     */
    private $clientId;

    /**
     * 
     * @var string
     */
    private $clientSecret;
    
    /**
     * 
     * @param array $config
     * @return void
     */
    protected function init(array $config)
    {
        if (!isset($config['client_id'], $config['client_secret'])) {
            show_error('Instagram requires client ID and client secret');
        }

        $this->clientId = $config['client_id'];
        $this->clientSecret = $config['client_secret'];
    }

    /**
     * 
     * @return string
     */
    public function getCertificatePath()
    {
        return dirname(__FILE__).'/Instagram/ssl.crt';
    }

    /**
     * 
     * @param string $tag
     * @return \Flare\Service\Instagram\Tag
     */
    public function getTagInfo($tag)
    {
        $request = new Request(self::HOST.'tags/'.$tag);
        $request->setOption('ssl_verifypeer', 1)
            ->setOption('ssl_verifyhost', 2)
            ->setOption('cainfo', $this->getCertificatePath())
            ->setParam('client_id', $this->clientId);

        $response = Curl::execute($request);

        if ($response->hasError()) {
            show_error($response->getError());
        }

        return new Tag($response->getBodyAsJson(), $this);
    }

    /**
     * 
     * @param string $tag
     * @return \Flare\Service\Instagram\Posts
     */
    public function getRecent($tag)
    {

    }

    /**
     * 
     * @param string $tag
     * @return \Flare\Service\Instagram\Posts
     */
    public function getPosts($tag)
    {

    }

    /**
     * 
     * @return \Flare\Service\Instagram\Posts
     */
    public function getPopular()
    {

    }

    /**
     * 
     * @param string $mediaId
     * @return \Flare\Service\Instagram\Post
     */
    public function getPost($mediaId)
    {
        
    }

    /**
     * 
     * @param int|float $lat
     * @param int|float $long
     * @return \Flare\Service\Instagram\Posts
     */
    public function getPostsByArea($lat = null, $long = null)
    {

    }
}