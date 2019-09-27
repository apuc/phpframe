<?php

namespace framework;

class Csrf
{
    const TOKEN_NAME = '_csrf_token';

    /**
     * @param string $tokenName
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public static function generateToken($tokenName = self::TOKEN_NAME)
    {
        $_SESSION[$tokenName] = sha1(uniqid(sha1(random_bytes(32)), true));
    }

    /**
     * @param string $tokenName
     * @return mixed
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public static function getToken($tokenName = self::TOKEN_NAME)
    {
        if (empty($_SESSION[$tokenName])) {
            static::generateToken($tokenName);
        }
        return $_SESSION[$tokenName];
    }

    public static function getTokenName()
    {
        return self::TOKEN_NAME;
    }

    /**
     * @param null $value
     * @param string $tokenName
     * @return bool
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public static function validate($value = null, $tokenName = self::TOKEN_NAME)
    {
        if (empty($_SESSION[$tokenName])) {
            static::generateToken($tokenName);
            return false;
        }

        if ($value === null && array_key_exists($tokenName, $_POST)) {
            $value = $_POST[$tokenName];
        }

        if (empty($value)) {
            return false;
        }

        return hash_equals($value, static::getToken($tokenName));
    }

    public static function getHiddenInputString($tokenName = self::TOKEN_NAME)
    {
        return sprintf('<input type="hidden" name="%s" value="%s"/>', $tokenName, static::getToken($tokenName));
    }

    public static function getQueryString($tokenName = self::TOKEN_NAME)
    {
        return sprintf('%s=%s', $tokenName, static::getToken($tokenName));
    }
}
