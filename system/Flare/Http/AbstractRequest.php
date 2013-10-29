<?php

namespace Flare\Http;

/**
 * 
 * @author anthony
 * 
 */
abstract class AbstractRequest
{
    /**
     * 
     * @var string
     */
    const METHOD_GET = 'GET';

    /**
     * 
     * @var string
     */
    const METHOD_POST = 'POST';

    /**
     * 
     * @var string
     */
    const METHOD_PUT = 'PUT';

    /**
     * 
     * @var string
     */
    const METHOD_DELETE = 'DELETE';
}