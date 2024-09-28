<?php
namespace fmr\util;

use fmr\system\exception\SystemException;
use fmr\TimeMonitoring;
use ParagonIE\ConstantTime\Hex;

/**
 * Contains string-related functions.
 */
final class StringUtil {

    const HTML_PATTERN = '~</?[a-z]+[1-6]?
			(?:\s*[a-z\-]+\s*(=\s*(?:
			"[^"\\\\]*(?:\\\\.[^"\\\\]*)*"|\'[^\'\\\\]*(?:\\\\.[^\'\\\\]*)*\'|[^\s>]
			))?)*\s*/?>~ix';

    const HTML_COMMENT_PATTERN = '~<!--(.*?)-->~';

    /**
     * utf8 bytes of the HORIZONTAL ELLIPSIS (U+2026)
     * @var string
     */
    const HELLIP = "\u{2026}";

    /**
     * utf8 bytes of the MINUS SIGN (U+2212)
     * @var string
     */
    const MINUS = "\u{2212}";

    /**
     * Returns a 40 character hexadecimal string generated using a CSPRNG.
     *
     * @return  string
     * @throws \Exception
     */
    public static function getRandomID() {
        return Hex::encode(\random_bytes(20));
    }

    /**
     * Creates an UUID.
     *
     * @return string
     * @throws \Exception
     */
    public static function getUUID(): string {
        return sprintf(
            '%04x%04x-%04x-%04x-%02x%02x-%04x%04x%04x',
            // time_low
            random_int(0, 0xffff),
            random_int(0, 0xffff),
            // time_mid
            random_int(0, 0xffff),
            // time_hi_and_version
            random_int(0, 0x0fff) | 0x4000,
            // clock_seq_hi_and_res
            random_int(0, 0x3f) | 0x80,
            // clock_seq_low
            random_int(0, 0xff),
            // node
            random_int(0, 0xffff),
            random_int(0, 0xffff),
            random_int(0, 0xffff)
        );
    }

    /**
     * Converts dos to unix newlines.
     *
     * @param string $string
     * @return string
     */
    public static function unifyNewlines($string): string {
        return \preg_replace("%(\r\n)|(\r)%", "\n", $string);
    }

    public static function parsePdoErrorMessage($message): array {
       //cfwDebug(debug_print_backtrace());
        $matches = [];
        if(strval($message) && $message != null) {

            if(str_contains($message, 'SQLSTATE[')) {
                preg_match('/SQLSTATE\[(\w+)\] \[(\w+)\] (.*)/', $message, $matches);

                if(empty($matches)) {
                    preg_match('/SQLSTATE\[(\w+)\](.*)/', $message, $matches);
                }
            }
        } else throw new SystemException("can not parse message",
            "Message can not be disassembled. Please check your regular expressions if necessaryMessage can not be disassembled. Please check your regular expressions if necessary",
        ['String' => $message, 'RegEx' => '/SQLSTATE\[(\w+)\] \[(\w+)\] (.*)/']);
        return $matches;
    }

    /**
     * Removes Unicode whitespace characters from the beginning
     * and ending of the given string.
     *
     * @param string $text
     * @return string
     */
    public static function trim($text): string {
        // These regular expressions use character properties
        // to find characters defined as space in the unicode
        // specification.
        // Do not merge the expressions, they are separated for
        // performance reasons.
        $text = \preg_replace('/^[\p{Zs}\s\x{202E}\x{200B}]+/u', '', $text);

        return \preg_replace('/[\p{Zs}\s\x{202E}\x{200B}]+$/u', '', $text);
    }

    /**
     * Converts javascript special characters.
     *
     * @param string $string
     * @return string
     */
    public static function encodeJS(string $string): string {
        $string = self::unifyNewlines($string);

        return \str_replace(["\\", "'", '"', "\n", "/"], ["\\\\", "\\'", '\\"', '\\n', '\\/'], $string);
    }

    /**
     * Converts html special characters.
     *
     * @param string $string
     * @return string
     */
    public static function encodeHTML($string): string {
        return @\htmlspecialchars(
            (string)$string,
            \ENT_QUOTES | \ENT_SUBSTITUTE | \ENT_HTML401,
            'UTF-8'
        );
    }

    /**
     * Decodes html entities.
     *
     * @param string $string
     * @return string
     */
    public static function decodeHTML($string): string {
        $string = \str_ireplace('&nbsp;', ' ', $string); // convert non-breaking spaces to ascii 32; not ascii 160

        return @\html_entity_decode(
            $string,
            \ENT_QUOTES | \ENT_SUBSTITUTE | \ENT_HTML401,
            'UTF-8'
        );
    }

    /**
     * Formats a numeric.
     *
     * @param number $numeric
     * @return string
     */
    public static function formatNumeric($numeric): string
    {
        if (\is_int($numeric)) {
            return self::formatInteger($numeric);
        } elseif (\is_float($numeric)) {
            return self::formatDouble($numeric);
        } else {
            if (\floatval($numeric) - (float)\intval($numeric)) {
                return self::formatDouble($numeric);
            } else {
                return self::formatInteger(\intval($numeric));
            }
        }
    }

    /**
     * Formats an integer.
     *
     * @param int $integer
     * @return string
     */
    public static function formatInteger($integer): string
    {
        $integer = self::addThousandsSeparator($integer);

        if ($integer < 0) {
            return self::formatNegative($integer);
        }

        return $integer;
    }

    /**
     * Formats a double.
     *
     * @param double $double
     * @param int    $maxDecimals
     * @return string
     */
    public static function formatDouble($double, $maxDecimals = 0): string {
        // round
        $double = (string)\round($double, ($maxDecimals > 0 ? $maxDecimals : 2));

        // consider as integer, if no decimal places found
        if (!$maxDecimals && \preg_match('~^(-?\d+)(?:\.(?:0*|00[0-4]\d*))?$~', $double, $match)) {
            return self::formatInteger($match[1]);
        }

        // remove last 0
        if ($maxDecimals < 2 && \substr($double, -1) == '0') {
            $double = \substr($double, 0, -1);
        }

        // replace decimal point
        $double = \str_replace('.', ".", $double);

        // add thousands separator
        $double = self::addThousandsSeparator($double);

        // format minus
        return self::formatNegative($double);
    }

    /**
     * Adds thousands separators to a given number.
     *
     * @param mixed $number
     * @return string
     */
    public static function addThousandsSeparator($number): string {
        if ($number >= 1000 || $number <= -1000) {
            $number = \preg_replace(
                '~(?<=\d)(?=(\d{3})+(?!\d))~',
                ".",
                $number
            );
        }

        return $number;
    }

    /**
     * Replaces the MINUS-HYPHEN with the MINUS SIGN.
     *
     * @param mixed $number
     * @return string
     */
    public static function formatNegative($number): string {
        return \str_replace('-', self::MINUS, $number);
    }

    /**
     * Alias to php ucfirst() function with multibyte support.
     *
     * @param string $string
     * @return string
     */
    public static function firstCharToUpperCase($string): string {
        return \mb_strtoupper(\mb_substr($string, 0, 1)) . \mb_substr($string, 1);
    }

    /**
     * Alias to php lcfirst() function with multibyte support.
     *
     * @param string $string
     * @return string
     */
    public static function firstCharToLowerCase($string): string {
        return \mb_strtolower(\mb_substr($string, 0, 1)) . \mb_substr($string, 1);
    }

    /**
     * Alias to php mb_convert_case() function.
     *
     * @param string $string
     * @return string
     */
    public static function wordsToUpperCase($string): string {
        return \mb_convert_case($string, \MB_CASE_TITLE);
    }

    /**
     * Alias to php str_ireplace() function with UTF-8 support.
     *
     * This function is considered to be slow, if $search contains
     * only ASCII characters, please use str_ireplace() instead.
     *
     * @param string $search
     * @param string $replace
     * @param string $subject
     * @param int    $count
     * @return string
     */
    public static function replaceIgnoreCase($search, $replace, $subject, &$count = 0): string {
        $startPos = \mb_strpos(\mb_strtolower($subject), \mb_strtolower($search));
        if ($startPos === false) {
            return $subject;
        } else {
            $endPos = $startPos + \mb_strlen($search);
            $count++;

            return \mb_substr($subject, 0, $startPos) . $replace . self::replaceIgnoreCase(
                    $search,
                    $replace,
                    \mb_substr($subject, $endPos),
                    $count
                );
        }
    }

    /**
     * Returns true if the given string is utf-8 encoded.
     * @see     http://www.w3.org/International/questions/qa-forms-utf-8
     *
     * @param string $string
     * @return  bool
     */
    public static function isUTF8($string)
    {

        return !!\preg_match('/^(
				[\x09\x0A\x0D\x20-\x7E]*		# ASCII
			|	[\xC2-\xDF][\x80-\xBF]			# non-overlong 2-byte
			|	\xE0[\xA0-\xBF][\x80-\xBF]		# excluding overlongs
			|	[\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}	# straight 3-byte
			|	\xED[\x80-\x9F][\x80-\xBF]		# excluding surrogates
			|	\xF0[\x90-\xBF][\x80-\xBF]{2}		# planes 1-3
			|	[\xF1-\xF3][\x80-\xBF]{3}		# planes 4-15
			|	\xF4[\x80-\x8F][\x80-\xBF]{2}		# plane 16
			)*$/x', $string);
    }

    /**
     * Escapes the closing cdata tag.
     *
     * @param string $string
     * @return string
     */
    public static function escapeCDATA($string): string {
        return \str_replace(']]>', ']]]]><![CDATA[>', $string);
    }

    /**
     * Adds a trailing slash to the given path.
     *
     * @param string $path
     * @return string
     */
    public static function addTrailingSlash(string $path): string {
        return \rtrim($path, '/') . '/';
    }


}