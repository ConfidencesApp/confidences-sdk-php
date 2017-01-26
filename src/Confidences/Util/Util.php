<?php

namespace Confidences\Util;

abstract class Util
{
    private static $isMbstringAvailable = null;

    /**
     * @param string|mixed $value A string to UTF8-encode.
     *
     * @return string|mixed The UTF8-encoded string, or the object passed in if
     *    it wasn't a string.
     */
    public static function utf8($value)
    {
        if (self::$isMbstringAvailable === null) {
            self::$isMbstringAvailable = function_exists('mb_detect_encoding');

            // @codeCoverageIgnoreStart
            if (!self::$isMbstringAvailable) {
                trigger_error(
                    "It looks like the mbstring extension is not enabled. " .
                    "UTF-8 strings will not properly be encoded. Ask your system " .
                    "administrator to enable the mbstring extension, or write to " .
                    "support@confidences.co if you have any questions.",
                    E_USER_WARNING
                );
            }
            // @codeCoverageIgnoreEnd
        }

        if (is_string($value) && self::$isMbstringAvailable && mb_detect_encoding($value, "UTF-8", true) != "UTF-8") {
            // @codeCoverageIgnoreStart
            return utf8_encode($value);
            // @codeCoverageIgnoreEnd
        } else {
            return $value;
        }
    }
}
