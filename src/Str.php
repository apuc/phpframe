<?php

namespace framework;

class Str
{
    public static function startsWith($string, $with)
    {
        return $with === '' || strpos($string, $with) === 0;
    }

    public static function endsWith($string, $with)
    {
        return $with === '' || substr($string, -strlen($with)) === $with;
    }

    public static function toSnakeCase($string, $delimeter = '_')
    {
        return strtolower(preg_replace_callback('/([a-z])([A-Z])/', function ($match) use ($delimeter) {
            return $match[1] . $delimeter . strtolower($match[2]);
        }, $string));
    }

    public static function toCamelCase($string, $capitalizeFirstChar = false)
    {
        $string = preg_replace_callback('/_([a-z])/', function ($match) {
            return strtoupper($match[1][0]);
        }, $string);

        return $capitalizeFirstChar ? ucfirst($string) : $string;
    }

    public static function zeroPad($number, $length)
    {
        return str_pad($number, $length, '0', STR_PAD_LEFT);
    }
}
