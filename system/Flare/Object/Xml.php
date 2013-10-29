<?php

namespace Flare\Object;

use SimpleXMLElement;
use Flare\Object;

/**
 * 
 * @author anthony
 * 
 */
class Xml extends Object
{
    /**
     * 
     * @param string|array $xmldata
     * @param boolean $isPathOrUrl
     */
    public function __construct($xmldata, $isPathOrUrl = false)
    {
        $this->_data = new SimpleXMLElement($xmldata);
    }
}