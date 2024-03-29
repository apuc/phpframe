<?php

namespace framework\web;

class Cookie
{
    public static function get($name, $default = null)
    {
        return array_key_exists($name, $_COOKIE) ? $_COOKIE[$name] : $default;
    }

    public static function set($name, $value, $period, $path = '/', $domain = null, $secure = false, $httpOnly = false)
    {
        setcookie($name, $value, time() + $period, $path, $domain, $secure, $httpOnly);
    }

    public static function remove($name)
    {
        $value = self::get($name);
        setcookie($name, '', 0, '/');

        return $value;
    }
}
