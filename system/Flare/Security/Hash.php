<?php

namespace Flare\Security;

use Flare\Security;

/**
 * 
 * @author anthony
 * 
 */
class Hash extends Security
{
    /**
     * 
     * @var string
     */
    const TYPE_OPENSSL = 'openssl';

    /**
     * 
     * @var string
     */
    const TYPE_HASH = 'hash';

    /**
     * 
     * @var string
     */
    const TYPE_MHASH = 'mhash';

    /**
     * 
     * @var array
     */
    private static $_supportedAlgosOpenssl = array(
        'md2',
        'md4',
        'mdc2',
        'rmd160',
        'sha',
        'sha1',
        'sha224',
        'sha256',
        'sha384',
        'sha512'
    );

    /**
     * 
     * @var array
     */
    private static $_supportedAlgosMhash = array(
        'adler32',
        'crc32',
        'crc32b',
        'gost',
        'haval128',
        'haval160',
        'haval192',
        'haval256',
        'md4',
        'md5',
        'ripemd160',
        'sha1',
        'sha256',
        'tiger',
        'tiger128',
        'tiger160'
    );

    /**
     * 
     * @param string $str
     * @param string $algo
     * @param bool $binaryOutput
     * @return string
     */
    public static function create($str, $algo = 'SHA512', $binaryOutput = false)
    {
        $result = null;
        $algo = strtolower($algo);
        if (function_exists($algo)) {
            return $algo($str, $binaryOutput);
        }
        $type = self::getHashSupport($algo);
        if ($type) {
            $supportedMethod = '_digest'.ucfirst($type);
            $result = self::$supportedMethod($algo, $str, $binaryOutput);
        } else {
            show_error("{$algo} is not supported by your server");
        }
        return $result;
    }

    /**
     * 
     * @param string $algo
     * @return string
     */
    public static function getHashSupport($algo)
    {
        $support = null;
        if (function_exists('hash') && in_array($algo, hash_algos())) {
            $support = self::TYPE_HASH;
        } elseif (function_exists('mhash') && in_array($algo, self::$_supportedAlgosMhash)) {
            $support = self::TYPE_MHASH;
        } elseif (function_exists('openssl_digest')) {
            if ($algo == 'ripemd160') {
                $algo = 'rmd160';
            }
            if (in_array($algo, self::$_supportedAlgosOpenssl)) {
               $support = self::TYPE_OPENSSL;
            }
        }
        return $support;
    }

    /**
     * 
     * @param string $algorithm
     * @param string $data
     * @param bool $binaryOutput
     * @return string
     */
    private static function _digestHash($algorithm, $data, $binaryOutput)
    {
        return hash($algorithm, $data, $binaryOutput);
    }

    /**
     * 
     * @param string $algorithm
     * @param string $data
     * @param bool $binaryOutput
     * @return string
     */
    private static function _digestMhash($algorithm, $data, $binaryOutput)
    {
        $constant = constant('MHASH_' . strtoupper($algorithm));
        $binary = mhash($constant, $data);
        if ($binaryOutput) {
            return $binary;
        }
        return bin2hex($binary);
    }

    /**
     * 
     * @param string $algorithm
     * @param string $data
     * @param bool $binaryOutput
     * @return string
     */
    private static function _digestOpenssl($algorithm, $data, $binaryOutput)
    {
        if ($algorithm == 'ripemd160') {
            $algorithm = 'rmd160';
        }
        return openssl_digest($data, $algorithm, $binaryOutput);
    }
}