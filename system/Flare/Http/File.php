<?php

namespace Flare\Http;

use Flare\Security\File as FileSec;
use Flare\Security\Hash;
use Flare\Object\Image;
use Flare\Http\Files;

/**
 * 
 * @author anthony
 * 
 */
class File
{
    /**
     * 
     * @var array
     */
    private static $_instances = array();

    /**
     * 
     * 
     */
    private static $_removeMultipleExtension = false;

    /**
     * 
     * @var array
     */
    private static $_defaultOptions = array(
        'is_image' => null,
        'max_size' => null,
        'min_size' => null,
        'max_height' => null,
        'max_width' => null,
        'types' => null,
        'filename' => null
    );

    /**
     * 
     * @var array
     */
    private static $_errorCodes = array(
        UPLOAD_ERR_INI_SIZE => 'Upload file exceeds limit', // 1
        UPLOAD_ERR_FORM_SIZE => 'Upload file exceeds form limit', // 2
        UPLOAD_ERR_PARTIAL => 'Upload file partial', // 3
        UPLOAD_ERR_NO_FILE => 'Upload no file selected', // 4
        UPLOAD_ERR_NO_TMP_DIR => 'Upload no temp directory', // 6
        UPLOAD_ERR_CANT_WRITE => 'Upload unable to write file', // 7
        UPLOAD_ERR_EXTENSION => 'Upload stopped by extension', // 8
        0 => 'Unkown error' // 0
        // UPLOAD_ERR_OK => 'No Error'
    );

    /**
     * 
     * @var string
     */
    private $_name;

    /**
     * 
     * @var string
     */
    private $_tmpname;

    /**
     * 
     * @var string
     */
    private $_filename;

    /**
     * 
     * @var string
     */
    private $_extension;

    /**
     * 
     * @var string
     */
    private $_type;

    /**
     * 
     * @var int
     */
    private $_error;

    /**
     * 
     * @var int
     */
    private $_size;

    /**
     * 
     * @var string
     */
    private $_mimeType;

    /**
     * 
     * @var boolean
     */
    private $_uploaded = false;

    /**
     * 
     * @var boolean
     */
    private $_isImage = false;

    /**
     * 
     * @var string
     */
    private $_uploadError = null;

    /**
     * 
     * @var int
     */
    private $_width = 0;

    /**
     * 
     * @var int
     */
    private $_height = 0;

    /**
     * 
     * @param string $name
     * @return \Flare\Http\File|null
     */
    public static function get($name)
    {
        if (!isset(self::$_instances[$name])) {
            if (empty($_FILES[$name]['name'])) {
                return null;
            } elseif (is_array($_FILES[$name]['name'])) {
                show_error("File uploaded is multiple. Please use getMultiple()");
            }
            self::$_instances[$name] = new self($name);
        }
        return self::$_instances[$name];
    }

    /**
     * 
     * @param string $name
     * @return \Flare\Http\Files
     */
    public static function getMultiple($name)
    {
        if (!isset(self::$_instances[$name])) {
            if (empty($_FILES[$name]['name'])) {
                return null;
            } elseif (!is_array($_FILES[$name]['name'])) {
                show_error("File uploaded is single. Please use get()");
            }
            $i = 0;
            $files = array();
            $count = count($_FILES[$name]['name']);
            while ($i < $count) $files[] = new self($name, $i++);
            self::$_instances[$name] = new Files($files);
        }
        return self::$_instances[$name];
    }

    /**
     * 
     * @return array
     */
    public static function getErrorCodes()
    {
        return self::$_errorCodes;
    }

    /**
     * 
     * @param string $base64String
     * @param string $path
     * @return \Flare\Http\File|boolean
     */
    public static function uploadFromString($base64String, $path)
    {
        $result = false;
        $source = explode(',', $base64String, 2);
        if (count($source) !== 2) {
            show_error("Invalid base64 string");
        }

        $type = substr(substr($source[0], 0, -7), 5);
        $ext = explode('/', $type);
        $createpath = realpath($path);
        $createpath = $createpath !== false ? rtrim(str_replace("\\", "/", $createpath), "/") : rtrim($path, "/");
        
        if (@is_dir($createpath) === true) {
            $filename = Hash::create($source[1]).'.'.end($ext);
            $createpath .= '/'.$filename;
        } else {
            $filename = pathinfo($createpath, PATHINFO_FILENAME);
            $fileExt = pathinfo($createpath, PATHINFO_EXTENSION);
            if (!$fileExt) {
                $createpath .= $filename.'.'.end($ext);
            } else {
                $createpath .= $filename.'.'.$fileExt;
            }
        }
        if (file_put_contents($createpath, base64_decode(str_replace(' ', '+', $source[1])))) {
            $result = new self(null, $filename, $filename, $type, 0, 0);
        }
        return $result;
    }

    /**
     * 
     * @param string $name
     * @param int $index
     */
    private function __construct($name, $index = null)
    {
        $file = array();
        if ($index !== null) {
            $file = array(
                'tmp_name' => $_FILES[$name]['tmp_name'][$index],
                'name' => $_FILES[$name]['name'][$index],
                'error' => $_FILES[$name]['error'][$index],
                'size' => $_FILES[$name]['size'][$index]
            );
        } else {
            $file = $_FILES[$name];
        }

        $this->_name = $name;
        $this->_tmpname = $file['tmp_name'];
        $this->_error = (int) $file['error'];
        $this->_size = (int) $file['size'];
        $this->_filename = FileSec::sanitizeFilename($file['name']);
        $this->_extension = pathinfo($this->_filename, PATHINFO_EXTENSION);
        if (!$this->_error) {
            $this->_setMimeType($index);
            $this->_setAsImage();
        }
    }

    /**
     * 
     * @param int $index
     * @return void
     */
    private function _setMimeType($index = null)
    {
        $type = null;
        if (function_exists('finfo_file')) {
            $finfo = finfo_open(FILEINFO_MIME);
            if ($finfo) {
                $mime = finfo_file($finfo, $this->_tmpname);
                if ($mime && preg_match('/^([a-z\-]+\/[a-z0-9\-\.\+]+)(;\s.+)?$/', $mime, $match)) {
                    $type = $match[1];
                }
                finfo_close($finfo);
            }
        } elseif (DIRECTORY_SEPARATOR == '/' && function_exists('exec')) {
            $type = exec('file --brief --mime-type '.escapeshellarg($this->_tmpname));
        }

        if (!$type && function_exists('mime_content_type')) {
            $type = mime_content_type($this->_tmpname);
        }

        if ($index === null) {
            $this->_type = !$type ? $_FILES[$this->_name]['type'] : $type;
        } else {
            $this->_type = !$type ? $_FILES[$this->_name]['type'][$index] : $type;
        }
    }

    /**
     * 
     * @param string $filename
     * @return string
     */
    private function _removeMultipleExt($filename)
    {
        $exts = explode('.', $filename);
        $ext = array_pop($exts);
        $filename = array_shift($exts);

        foreach ($exts as $e) {
            
        }

        return $filename.'.'.$ext;
    }

    /**
     * 
     * @param array $rules
     * @return boolean
     */
    public function validate(array $rules)
    {
        $valid = true;
        if (!is_uploaded_file($this->_tmpname)) {
            $valid = false;
        } elseif (is_bool($rules['is_image'])) {
            if ($rules['is_image'] !== $this->isImage()) {
                $valid = false;
                $this->_setValidationError('Not an image');
            } elseif (($rules['max_width'] && $this->_width > (int) $rules['max_width'])
                || ($rules['max_height'] && $this->_height > (int) $rules['max_height']))
            {
                $valid = false;
                $this->_setValidationError('Not within required width');
            }
        }

        if ($valid) {
            if (is_array($rules['types']) && !empty($rules['types'])
                && !in_array($this->_extension, $rules['types']))
            {
                $this->_setValidationError('Invalid type');
                $valid = false;
            } elseif ($rules['min_size'] && $this->_size < (int) $rules['min_size']) {
                $valid = false;
                $this->_setValidationError('Must be greater that minimum size');
            } elseif ($rules['max_size'] && $this->_size > (int) $rules['max_size']) {
                $valid = false;
                $this->_setValidationError('Exceeds max size');
            }
        }
        
        return $valid;
    }

    /**
     * 
     * @return void
     */
    private function _setAsImage()
    {
        list($width, $height, $type) = @getimagesize($this->_tmpname);
        if ($type) {
            $type = image_type_to_mime_type($type);
            if ($type) {
                $this->_isImage = true;
                $this->_type = $type;
            }
            if ($width && $height) {
                $this->_width = $width;
                $this->_height = $height;
            }
        }
    }

    /**
     * 
     * @return boolean
     */
    public function isImage()
    {
        return $this->_isImage;
    }

    /**
     * 
     * @return string
     */
    public function getFilename()
    {
        return $this->_filename;
    }

    /**
     * 
     * @return string
     */
    public function getExtension()
    {
        return $this->_extension;
    }

    /**
     * 
     * @return string
     */
    public function getMimeType()
    {
        return $this->_type;
    }

    /**
     * 
     * @return string
     */
    public function getTempname()
    {
        return $this->_tmpname;
    }

    /**
     * 
     * @return int
     */
    public function getSize()
    {
        return $this->_size;
    }

    /**
     * 
     * @return int
     */
    public function getError()
    {
        return $this->_error;
    }

    /**
     * 
     * @return string
     */
    public function getErrorMessage()
    {
        if (!isset(self::$_errorCodes[$this->_error])) {
            return self::$_errorCodes[0];
        }
        return self::$_errorCodes[$this->_error];
    }

    /**
     * 
     * @return string
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     * 
     * @param string $destination
     * @param array $options
     * @return boolean|string
     */
    public function upload($destination, array $options = array())
    {
        $success = false;
        if ($this->_error) {
            return $success;
        }
        if ($options) {
            $options = array_merge(self::$_defaultOptions, $options);
        } else {
            $options = self::$_defaultOptions;
        }
        
        if (!empty($options['filename'])) {
            if (!pathinfo($options['filename'], PATHINFO_EXTENSION)) {
                $options['filename'] = $options['filename'].'.'.$this->_extension;
            }
            $options['filename'] = FileSec::sanitizeFilename($options['filename']);
        } else {
            $options['filename'] = $this->_filename;
        }

        $destination = $this->_validateUploadPath($destination);
        if ($destination) {
            if ($this->validate($options)) {
                if (@move_uploaded_file($this->_tmpname, $destination.$options['filename'])) {
                    $success = $options['filename'];
                    $this->_uploaded = true;
                } else {
                    $this->_setValidationError('Failed to upload');
                }
            }
        } else {
            $this->_setValidationError("Upload path doesn't exists");
        }
        return $success;
    }

    /**
     * 
     * @param string $error
     * @return void
     */
    private function _setValidationError($error)
    {
        $this->_uploadError = $error;
    }

    /**
     * 
     * @return string
     */
    public function getValidationError()
    {
        return $this->_uploadError;
    }

    /**
     * 
     * @return boolean
     */
    public function isUploaded()
    {
        return $this->_uploaded;
    }

    /**
     * 
     * @param string $to
     * @return string|boolean
     */
    private function _validateUploadPath($folder)
    {
        if (!$folder) return false;
        $moveTo = realpath($folder);
        $moveTo = $moveTo !== false ? rtrim(str_replace("\\", '/', $moveTo), '/').'/' : rtrim($folder, '/').'/';

        if (!@is_dir($moveTo)) {
            return false;
        }
        return $moveTo;
    }
}