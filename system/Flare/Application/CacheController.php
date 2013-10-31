<?php

namespace Flare\Application;

use Flare\Application\BaseController;

/**
 * 
 * @author anthony
 * 
 */
abstract class CacheController extends BaseController
{
    /**
     * 
     * @var string
     */
    protected $engine = 'Apc';

    /**
     * 
     * @return void
     */
    public function init() {}

    /**
     * 
     * @return void
     */
    public function complete() {}
}