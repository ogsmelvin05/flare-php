<?php

namespace Flare\Service\Instagram;

use Flare\Service\Instagram;
use Flare\Object\Json;

/**
 * 
 * @author anthony
 * 
 */
abstract class Object
{
    /**
     * 
     * @var \Flare\Service\Instagram
     */
    protected $instagram;

    /**
     * 
     * @var \Flare\Object\Json
     */
    protected $data;

    /**
     * 
     * @param \Flare\Object\Json $data
     * @param \Flare\Service\Instagram $instagram
     */
    public function __construct(Json $data, Instagram $instagram = null)
    {
        $this->data = $data;
        if ($instagram) {
            $this->instagram = $instagram;
        }
    }

    /**
     * 
     * @return boolean
     */
    public function hasError()
    {
        return !empty($this->data['meta']['error_message']);
    }

    /**
     * 
     * @return int
     */
    public function getErrorCode()
    {
        return $this->hasError() ? (int) $this->data['meta']['code'] : 0;
    }

    /**
     * 
     * @return string
     */
    public function getErrorMessage()
    {
        return $this->hasError() ? $this->data['meta']['error_message'] : '';
    }

    /**
     * 
     * @return string
     */
    public function getErrorType()
    {
        return $this->hasError() ? $this->data['meta']['error_type'] : '';
    }

    /**
     * 
     * @return mixed
     */
    public function __get($key)
    {
        return isset($this->data['data'][$key]) ? $this->data['data'][$key] : null;
    }
}