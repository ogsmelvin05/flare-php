<?php

namespace Flare\View\Response;

use Flare\Object\Json as JsonObject;
use Flare\View\Response;

/**
 * 
 * @author anthony
 * 
 */
class Json extends Response
{
    /**
     * 
     * @var string
     */
    protected $contentType = 'application/json';

    /**
     * 
     * @var \Flare\Object\Json
     */
    private $json;

    /**
     * 
     * @param \Flare\Object\Json|array|string $content
     * @param boolean $is_url
     */
    public function __construct($content)
    {
        if (!($content instanceof JsonObject)) {
            $content = new JsonObject($content);
        }
        $this->json = $content;
    }

    /**
     * 
     * @return string
     */
    public function render()
    {
        return (string) $this->json;
    }
}