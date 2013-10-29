<?php

namespace Flare\FileSystem;

use Flare\Security\File as FileSec;
use Flare\FileSystem\FileInfo;
use Flare\FileSystem\Action;

/**
 * 
 * @author anthony
 * 
 */
class File extends FileInfo implements Action
{
        /**
     * 
     * @param string $path
     * @param string $filename
     * @param boolean $autoAppendExtension
     * @return boolean
     */
    public function move($path, $filename = null, $autoAppendExtension = true)
    {
        if (!$this->exists()) {
            show_error("File '{$this->getPathname()}' doesn't exists");
        }

        if (!is_dir($path)) return false;

        $path = rtrim(str_replace("\\", '/', $path), '/').'/';
        if (!$filename) {
            $filename = $this->getFilename();
        }
        $path .= FileSec::sanitizeFilename($filename);
        if ($autoAppendExtension) {
            $path .= '.'.$this->getExtension();
        }

        if (@rename($this->getPathname(), $path)) {
            $path = realpath($path);
            if ($path) {
                parent::__construct(str_replace("\\", '/', $path));
                return true;
            }
        }
        return false;
    }

    /**
     * 
     * @param string $path
     * @param string $filename
     * @param boolean $autoAppendExtension
     * @return boolean
     */
    public function copy($path, $filename = null, $autoAppendExtension = true)
    {
        if (!$this->exists()) {
            show_error("File '{$this->getPathname()}' doesn't exists");
        }

        if (!is_dir($path)) return false;
        $path = rtrim(str_replace("\\", '/', $path), '/').'/';
        if (!$filename) {
            $filename = $this->getFilename();
        }
        $path .= FileSec::sanitizeFilename($filename);
        if ($autoAppendExtension) {
            $path .= '.'.$this->getExtension();
        }        

        if (@copy($this->_origPath, $path)) {
            $path = realpath($path);
            if ($path) {
                return true;
            }
        }
        return false;
    }
}