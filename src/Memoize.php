<?php

namespace framework;

class Memoize
{
    private static $cache = [];

    public static function memoize($key, \Closure $callback)
    {
        if (!array_key_exists($key, self::$cache)) {
            self::$cache[$key] = $callback();
        }

        return self::$cache[$key];
    }
}
