<?php

namespace Flare\Db;

use Flare\Flare as F;

/**
 * 
 * @author anthony
 * 
 */
abstract class Model
{
    /**
     * 
     * @return \Flare\Application\AbstractController
     */
    protected static function _getController()
    {
        return F::getApp()->getController();
    }

    /**
     * 
     * @return string|int
     */
    abstract public function save();

    /**
     * 
     * @return int
     */
    abstract public function trash();
}