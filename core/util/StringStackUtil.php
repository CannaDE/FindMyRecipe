<?php

namespace fmr\util;

/**
 * Replaces quoted strings in a text.
 */
final class StringStackUtil {
    /**
     * hash index
     * @var int
     */
    protected static $i = 0;

    /**
     * local string stack
     * @var string[][]
     */
    protected static $stringStack = [];

    /**
     * Replaces a string with an unique hash value.
     *
     * @param string $string
     * @param string $type
     * @param string $delimiter
     * @return  string      $hash
     */
    public static function pushToStringStack($string, $type = 'default', $delimiter = '@@')
    {
        self::$i++;
        $hash = $delimiter . StringUtil::getRandomID() . $delimiter;

        if (!isset(self::$stringStack[$type])) {
            self::$stringStack[$type] = [];
        }

        self::$stringStack[$type][$hash] = $string;

        return $hash;
    }

    /**
     * Reinserts strings that have been replaced by unique hash values.
     *
     * @param string $string
     * @param string $type
     * @return  string
     */
    public static function reinsertStrings($string, $type = 'default')
    {
        if (isset(self::$stringStack[$type])) {
            foreach (self::$stringStack[$type] as $hash => $value) {
                if (\str_contains($string, $hash)) {
                    $string = \str_replace($hash, $value, $string);
                    unset(self::$stringStack[$type][$hash]);
                }
            }
        }

        return $string;
    }

    /**
     * Returns the stack.
     *
     * @param string $type
     * @return  array
     */
    public static function getStack($type = 'default')
    {
        if (isset(self::$stringStack[$type])) {
            return self::$stringStack[$type];
        }

        return [];
    }

    /**
     * Forbid creation of StringStack objects.
     */
    private function __construct()
    {
        // does nothing
    }
}