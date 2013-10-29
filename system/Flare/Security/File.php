<?php

namespace Flare\Security;

use Flare\Security;

/**
 * 
 * @author anthony
 * 
 */
class File extends Security
{
    /**
     * 
     * @param string $filename
     * @param boolean $relativepath
     * @return string
     */
    public static function sanitizeFilename($filename, $relativepath = false)
    {
        $bad = array("<!--", "-->", "'", "<", ">", '"', '&', '$', '=', ';', '?',
                    '/', "%20", "%22",
                    "%3c",    // <
                    "%253c",    // <
                    "%3e",    // >
                    "%0e",    // >
                    "%28",    // (
                    "%29",    // )
                    "%2528",    // (
                    "%26",    // &
                    "%24",    // $
                    "%3f",    // ?
                    "%3b",    // ;
                    "%3d"      // =
                );
        if (!$relativepath) {
            $bad[] = './';
            $bad[] = '/';
        }
        $filename = self::removeInvisibleChars($filename, false);
        return stripslashes(str_replace($bad, '', $filename));
    }
}