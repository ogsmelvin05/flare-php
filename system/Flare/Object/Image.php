<?php

namespace Flare\Object;

if (!extension_loaded('gd') || !function_exists('gd_info')) {
    show_error("GD Library is not supported");
}

use Flare\Object;

/**
 * 
 * @author anthony
 * 
 */
class Image extends Object
{
    /**
     * 
     * @var resource
     */
    private $_image;

    /**
     * 
     * @var int
     */
    private $_type = IMAGETYPE_JPEG;

    /**
     * 
     * @var string
     */
    private $_path;

    /**
     * 
     * @var array
     */
    private $_fileinfo = array();

    /**
     * 
     * @param resource $resource
     * @param int $type
     * @param string $path
     */
    private function __construct($resource, $type, $path = null)
    {
        $this->_type = $type;
        $this->_image = $resource;
        if ($path) {
            $this->setPath($path);
        }
    }

    /**
     * 
     * @param string $path
     * @return \Flare\Object\Image
     */
    public function setPath($path)
    {
        $this->_path = $path;
        $this->_fileinfo = pathinfo($path);
        return $this;
    }

    /**
     * 
     * @return string
     */
    public function getPath()
    {
        return $this->_path;
    }

    /**
     * 
     * @return int
     */
    public function getWidth()
    {
        return imagesx($this->_image);
    }

    /**
     * 
     * @return string
     */
    public function getBasename()
    {
        if (!isset($this->_fileinfo['basename'])) {
            return null;
        }
        return $this->_fileinfo['basename'];
    }

    /**
     * 
     * @return string
     */
    public function getExtension()
    {
        if (!isset($this->_fileinfo['extension'])) {
            return null;
        }
        return $this->_fileinfo['extension'];
    }

    /**
     * 
     * @return string
     */
    public function getFilename()
    {
        if (!isset($this->_fileinfo['filename'])) {
            return null;
        }
        return $this->_fileinfo['filename'];
    }

    /**
     * 
     * @return int
     */
    public function getHeight()
    {
        return imagesy($this->_image);
    }

    /**
     * 
     * @param int $width
     * @param int $height
     * @param int $type
     * @param string $path
     * @return \Flare\Object\Image
     */
    public static function create($width, $height, $type = IMAGETYPE_JPEG, $path = null)
    {
        return new self(imagecreatetruecolor($width, $height), $type, $path);
    }

    /**
     * 
     * @return resource
     */
    public function getResource()
    {
        return $this->_image;
    }

    /**
     * 
     * @param string $path
     * @return \Flare\Object\Image
     */
    public static function load($path)
    {
        $path = realpath($path);
        if (!$path) {
            show_error('Invalid path');
        }

        $type = null;
        $resource = null;
        $image = getimagesize($path);
        if (isset($image[2]) && in_array($image[2], get_image_types())) {
            $type = $image[2];
            if ($type === IMAGETYPE_JPEG) {
                $resource = imagecreatefromjpeg($path);
            } elseif ($type === IMAGETYPE_GIF) {
                $resource = imagecreatefromgif ($path);
            } elseif ($type === IMAGETYPE_PNG) {
                $resource = imagecreatefrompng($path);
            } else {
                show_error("Can't load image, not supported image type");
            }
        } else {
            show_error("Not a valid image type");
        }
        return new self($resource, $type, $path);
    }

    /**
     * 
     * @param resource $resource
     * @param int $image_type
     * @return \Flare\Object\Image
     */
    public static function loadFromResource($resource, $image_type)
    {
        return new self($resource, $image_type);
    }

    /**
     * 
     * @param int $newwidth
     * @param int $newheight
     * @return \Flare\Object\Image
     */
    private function _resize($newwidth, $newheight)
    {
        $dst = imagecreatetruecolor($newwidth, $newheight);
        if (!imagecopyresampled($dst, $this->_image, 0, 0, 0, 0, $newwidth, $newheight, $this->getWidth(), $this->getHeight())) {
            return null;
        }
        return new self($dst, $this->_type, $this->_path);
    }

    /**
     * 
     * @return string $path
     * @param int $type
     * @param int $compression
     * @return boolean
     */
    public function save($path, $type = null, $compression = 75)
    {
        $success = false;
        if (!$type) {
            $type = $this->_type;
        }
        if ($type === IMAGETYPE_JPEG) {
            $success = imagejpeg($this->_image, $path, $compression);
        } elseif ($type === IMAGETYPE_GIF) {
            $success = imagegif ($this->_image, $path);      
        } elseif ($type === IMAGETYPE_PNG) {
            $success = imagepng($this->_image, $path);
        }
        return $success;
    }

    /**
     * 
     * @return boolean
     */
    public function overwrite()
    {
        if (!($path = $this->getPath())) {
            return false;
        }
        return $this->save($path);
    }

    /**
     * 
     * @param int $width
     * @param int $height
     * @return \Flare\Object\Image
     */
    public function resize($width, $height)
    {
        return $this->_resize($width, $height);
    }

    /**
     * 
     * @param int $height
     * @return \Flare\Object\Image
     */
    public function resizeHeight($height)
    {
        $ratio = $height / $this->getHeight();
        $width = $this->getWidth() * $ratio;
        return $this->_resize($width, $height);
    }

    /**
     * 
     * @param int $width
     * @return \Flare\Object\Image
     */
    public function resizeWidth($width)
    {
        $ratio = $width / $this->getWidth();
        $height = $this->getheight() * $ratio;
        return $this->_resize($width, $height);
    }
}