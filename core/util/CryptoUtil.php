<?php

namespace fmr\util;

use fmr\system\exception\SystemException;
use ParagonIE\ConstantTime\Base64;

/**
 * Contains cryptographic helper functions.
 * Features:
 * - Creating secure signatures based on the Keyed-Hash Message Authentication Code algorithm
 *
 **/
final class CryptoUtil
{

    /**
     * Signs the given value with the signature secret.
     *
     * @param string $value
     *
     * @return string
     * @throws SystemException
     */
    public static function getSignature(string $value): string
    {
        if (\mb_strlen(SIGNATURE_SECRET, '8bit') < 15) {
            throw new SystemException('SIGNATURE_SECRET is too short, aborting.');
        }

        return \hash_hmac('sha256', $value, SIGNATURE_SECRET);
    }

    /**
     * Creates a signed (signature + encoded value) string.
     *
     * @param string $value
     *
     * @return string
     * @throws SystemException
     * @throws AjaxException
     */
    public static function createSignedString(string $value): string
    {
        return self::getSignature($value) . '-' . Base64::encode($value);
    }

    /**
     * Returns whether the given string is a proper signed string.
     * (i.e. consists of a valid signature + encoded value)
     *
     * @param string $string
     *
     * @return bool
     * @throws AjaxException
     * @throws SystemException
     */
    public static function validateSignedString(string $string): bool
    {
        $parts = \explode('-', $string, 2);
        if (\count($parts) !== 2) {
            return false;
        }
        [$signature, $value] = $parts;

        try {
            $value = Base64::decode($value);
        } catch (\RangeException $e) {
            return false;
        }

        return \hash_equals($signature, self::getSignature($value));
    }

    /**
     * Returns the value of a signed string, after
     * validating whether it is properly signed.
     *
     * - Returns null if the string is not properly signed.
     *
     * @param string $string
     *
     * @return string|null
     * @throws AjaxException
     * @throws SystemException
     */
    public static function getValueFromSignedString(string $string): ?string
    {
        if (!self::validateSignedString($string)) {
            return null;
        }

        $parts = \explode('-', $string, 2);
        try {
            return Base64::decode($parts[1]);
        } catch (\RangeException $e) {
            throw new \LogicException('Unreachable', 0, $e);
        }
    }

    /**
     * Forbid creation of CryptoUtil objects.
     */
    private function __construct()
    {
        throw new SystemException("CryptoUtil can not use as object => static only.");
    }
}