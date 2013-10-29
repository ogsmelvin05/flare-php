<?php

namespace Flare\Service\Instagram;

use Flare\Service\Instagram\Object;

/**
 * 
 * @author anthony
 * 
 */
class Tag extends Object
{
    /**
     * 
     * @return int
     */
    public function getCount()
    {
        return $this->media_count;
    }

    /**
     * 
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
}