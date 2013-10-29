<?php

namespace Flare\Http;

use ArrayObject;

/**
 * 
 * @author anthony
 * 
 */
class Files extends ArrayObject
{
    /**
     * 
     * @var string
     */
    private $_error;

    /**
     * 
     * @param array $files
     */
    public function __construct(array $files)
    {
        parent::__construct($files);
    }

    /**
     * 
     * @return string
     */
    public function getValidationError()
    {
        return $this->_error;
    }

    /**
     * 
     * @param string $destination
     * @param array $options
     * @param boolean $autoCreateFolder
     * @return boolean
     */
    public function upload($destination, array $options = array(), $autoCreateFolder = false)
    {
        $success = true;
        foreach ($this as $file) {
            if (!$file->upload($destination, $options, $autoCreateFolder)) {
                $this->_error = $file->getValidationError();
                $success = false;
                break;
            }
        }
        return $success;
    }
}