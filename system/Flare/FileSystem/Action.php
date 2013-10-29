<?php

namespace Flare\FileSystem;

/**
 * 
 * @author anthony
 * 
 */
interface Action
{
    /**
     * 
     * @param string $path
     * @param string $filename
     * @param boolean $autoAppendExtension
     * @return boolean
     */
    public function move($path, $filename = null, $autoAppendExtension = true);

    /**
     * 
     * @param string $path
     * @param string $filename
     * @param boolean $autoAppendExtension
     * @return boolean
     */
    public function copy($path, $filename = null, $autoAppendExtension = true);
}