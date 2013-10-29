<?php

namespace Flare\Db\Sql\Model;

use Flare\Db\Sql\Model;

/**
 * 
 * @author anthony
 * 
 */
class Relation
{
    /**
     * 
     * @var \Flare\Db\Sql\Model
     */
    private $_model;

    /**
     * 
     * @param \Flare\Db\Sql\Model $model
     */
    public function __construct(Model &$model)
    {
        $this->_model = & $model;
    }
}