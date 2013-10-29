<?php

namespace Flare\Db\Sql\Result;

use Flare\Db\Sql\Model;

/**
 * 
 * @author anthony
 * 
 */
class Row extends Model
{
    /**
     * 
     * @param string $table
     * @param array $data
     * @param string $primaryKey
     */
    public function __construct($table, array $data = array(), $primaryKey = null)
    {
        $this->table = $table;
        if ($primaryKey) {
            $this->primaryKey = $primaryKey;
        }
        parent::__construct($data);
    }
}