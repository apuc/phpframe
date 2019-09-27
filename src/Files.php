<?php

namespace framework;

class Files
{
    public static function exists($path)
    {
        return file_exists($path);
    }

    public static function get($path)
    {
        return is_file($path) ? @file_get_contents($path) : null;
    }

    public static function put($path, $data)
    {
        @file_put_contents($path, $data);
    }

    public static function append($path, $data)
    {
        @file_put_contents($path, $data, FILE_APPEND);
    }

    public static function extension($path)
    {
        return pathinfo($path, PATHINFO_EXTENSION);
    }

    public static function makeDirectory($path, $mode = 0755, $recursive = true)
    {
        if (is_dir($path)) {
            return;
        }

        if (!@mkdir($path, $mode, $recursive) && !is_dir($path)) {
            throw new \RuntimeException("Directory $path not created");
        }
    }
}
