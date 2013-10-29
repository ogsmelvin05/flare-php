<?php

namespace Flare\FileSystem;

use Flare\Security\File as FileSec;
use UnexpectedValueException;
use Flare\FileSystem\Action;
use Flare\FileSystem\File;
use FilesystemIterator;

/**
 * 
 * @author anthony
 * 
 */
class Directory extends FilesystemIterator implements Action
{
    /**
     * 
     * @var string
     */
    private $_origPath;

    /**
     * 
     * @var boolean
     */
    private $_isValid = false;

    /**
     * 
     * @param string $path
     */
    public function __construct($path)
    {
        $this->_origPath = realpath($path);
        if ($this->_origPath) {
            $this->_origPath = rtrim(str_replace("\\", '/', $this->_origPath), '/');
            $this->_isValid = true;
        }
        try {
            parent::__construct($this->_origPath, self::UNIX_PATHS);
        } catch (UnexpectedValueException $ex) {}
    }

    /**
     * 
     * @return string
     */
    public function getPerms()
    {
        return substr(sprintf('%o', parent::getPerms()), -4);
    }

    /**
     * 
     * @param boolean $convert
     * @return string|int
     */
    public function getSize($convert = false)
    {
        $size = parent::getSize();
        if ($convert) {
            $units = array('B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
            $power = $size > 0 ? floor(log($size, 1024)) : 0;
            return number_format($size / pow(1024, $power), 2, '.', ',').' '.$units[$power];
        }
        return $size;
    }

    /**
     * 
     * @param string $dateFormat
     * @return string
     */
    public function getMTime($dateFormat = 'Y-m-d H:i:s')
    {
        return date($dateFormat, parent::getMTime());
    }

    /**
     * 
     * @param string $dateFormat
     * @return string
     */
    public function getCTime($dateFormat = 'Y-m-d H:i:s')
    {
        return date($dateFormat, parent::getCTime());
    }

    /**
     * 
     * @param string $dateFormat
     * @return string
     */
    public function getATime($dateFormat = 'Y-m-d H:i:s')
    {
        return date($dateFormat, parent::getATime());
    }

    /**
     * 
     * @return \Flare\FileSystem\File
     */
    public function current()
    {
        return new File(parent::current());
    }

    /**
     * 
     * @return boolean
     */
    public function exists()
    {
        return $this->_isValid;
    }

    /**
     * 
     * @return \Flare\FileSystem\Directory|null
     */
    public function getParent()
    {
        if (!$this->hasParent()) {
            return null;
        }
        $dir = new Directory($this->getPath());
        if (!$dir->exists()) {
            return null;
        }

        return $dir;
    }

    /**
     * 
     * @return string
     */
    public function getPath()
    {
        $path = parent::getPath();
        if ($this->_origPath === $path) {
            return str_replace("\\", '/', dirname($this->_origPath));
        }
        return $path;
    }

    /**
     * 
     * @return string
     */
    public function getPathname()
    {
        $pathname = parent::getPathname();
        if ($this->_origPath !== $pathname) {
            return $this->_origPath;
        }
        return $pathname;
    }

    /**
     * 
     * @return string
     */
    public function getFilename()
    {
        $filename = parent::getFilename();
        $origFilename = basename($this->_origPath);
        if ($origFilename !== $filename) {
            return $origFilename;
        }
        return $filename;
    }

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
            show_error("Directory '{$this->getPathname()}' doesn't exists");
        }

        if (!is_dir($path)) return false;

        $path = rtrim(str_replace("\\", '/', $path), '/').'/';
        if (!$filename) {
            $filename = $this->getFilename();
        }
        $path .= FileSec::sanitizeFilename($filename);
        if ($autoAppendExtension && $this->getExtension()) {
            $path .= '.'.$this->getExtension();
        }        

        if (rename($this->getPathname(), $path)) {
            $path = realpath($path);
            if ($path) {
                parent::__construct(rtrim(str_replace("\\", '/', $path), '/'));
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
            show_error("Directory '{$this->getPathname()}' doesn't exists");
        }

        if (!is_dir($path)) return false;

        $path = rtrim(str_replace("\\", '/', $path), '/').'/';
        if (!$filename) {
            $filename = $this->getFilename();
        }
        $path .= FileSec::sanitizeFilename($filename);

        return $this->_copy($this->getPathname(), $path);
    }

    /**
     * 
     * @param string $src
     * @param string $dst
     * @return void
     */
    private function _copy($src, $dst)
    {
        $dir = opendir($src);
        @mkdir($dst);
        if (!file_exists($dst)) {
            return false;
        }
        while (false !== ($file = readdir($dir))) {
            if (($file != '.') && ($file != '..')) {
                if (is_dir($src.'/'.$file)) {
                    $this->_copy($src.'/'.$file, $dst.'/'.$file);
                } else {
                    if (!copy($src.'/'.$file, $dst.'/'.$file)) {
                        return false;
                    }
                }
            }
        }
        closedir($dir);
        return true;
    }

    /**
     * 
     * @return boolean
     */
    public function isHidden()
    {
        return (strpos($this->getFilename(), '.') === 0);
    }

    /**
     * 
     * @return boolean
     */
    public function hasParent()
    {
        return (dirname($this->getPath()) !== $this->getPath());
    }
}