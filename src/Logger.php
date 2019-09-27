<?php

namespace framework;

class Logger
{
    public const TRACE = 0;
    public const DEBUG = 1;
    public const INFO = 2;
    public const WARN = 3;
    public const ERROR = 4;

    private static $levels = [
        self::TRACE => 'TRACE',
        self::DEBUG => 'DEBUG',
        self::INFO => 'INFO',
        self::WARN => 'WARN',
        self::ERROR => 'ERROR'
    ];

    private const PARAM_MASK = '{}';

    public static function log($level = self::INFO, $message, ...$params)
    {
        if ($level < self::getLevel()) {
            return;
        }

        if (is_array($message)) {
            $message = print_r($message, true);
        }

        if (is_string($message) && !empty($params)) {
            $message = self::getMessageWithParams($message, ...$params);
        }

        if (is_object($message)) {
            ob_start();
            var_dump($message);
            $message = ob_get_clean();
        }

        $filename = sprintf('../app/logs/%s.log', date('Y-m-d'));
        if (!is_dir(dirname($filename))) {
            @mkdir(dirname($filename), 0777, true);
        }

        $message = date('Y.m.d H:i:s') . ' [' . self::$levels[$level] . '] ' . $message;
        file_put_contents($filename, $message . PHP_EOL, FILE_APPEND);
    }

    public static function trace($message, ...$params)
    {
        self::log(self::TRACE, $message, ...$params);
    }

    public static function debug($message, ...$params)
    {
        self::log(self::DEBUG, $message, ...$params);
    }

    public static function info($message, ...$params)
    {
        self::log(self::INFO, $message, ...$params);
    }

    public static function warn($message, ...$params)
    {
        self::log(self::WARN, $message, ...$params);
    }

    public static function error($message, ...$params)
    {
        self::log(self::ERROR, $message, ...$params);
    }

    public static function getMessageWithParams($message, ...$params)
    {
        foreach ($params as $param) {
            if (($pos = strpos($message, self::PARAM_MASK)) !== false) {
                if (is_array($param) || is_object($param)) {
                    $param = json_encode($param);
                }
                $message = implode('', [
                    substr($message, 0, $pos),
                    $param,
                    substr($message, $pos + strlen(self::PARAM_MASK))
                ]);
            }
        }

        return $message;
    }

    private static $level;

    private static function getLevel()
    {
        if (self::$level === null) {
            self::$level = self::WARN;
            $levelName = App::getConfigValue(['logging', 'level']);
            if ($levelName !== null) {
                foreach (self::$levels as $level => $name) {
                    if (strcasecmp($name, $levelName) === 0) {
                        self::$level = $level;
                        break;
                    }
                }
            }
        }
        return self::$level;
    }
}
