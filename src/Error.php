<?php

namespace framework;

use Exception;
use framework\web\Request;

class Error
{
    public static function handle()
    {
        $args = \func_get_args();

        if (\count($args) === 5) {
            list(, $text, $file, $line, $info) = $args;
            if (\is_array($info)) {
                $info = print_r($info, true);
            }
            $code = 500;
        } else {
            /** @var Exception $exception */
            $exception = $args[0];
            $code = $exception->getCode();
            $text = $exception->getMessage();
            $file = $exception->getFile();
            $line = $exception->getLine();
            $info = $exception->getTraceAsString();
        }

        $errors = [
            401 => 'Unauthorized',
            403 => 'Forbidden',
            404 => 'Not Found',
            500 => 'Internal Server Error'
        ];

        if (!array_key_exists($code, $errors)) {
            $code = 500;
        }

        if (!headers_sent()) {
            header(sprintf('HTTP/1.0 %d %s', $code, $errors[$code]), true, $code);
        }

        $message = "{$code} {$text} {$file}:{$line}";
        if ($info !== null) {
            $message .= "\n{$info}";
        }

        Logger::error($message);

        $data = [
            'code' => $code,
            'title' => $errors[$code],
            'message' => $message,
            'debug' => App::getConfigValue('debug', false)
        ];

        $errorView = App::getConfigValue(['views', 'error'], null);
        $viewsPath = App::getConfigValue(['views', 'views_path'], '../app/views');

        if ($errorView !== null && is_readable($viewsPath . DIRECTORY_SEPARATOR . $errorView)) {
            echo App::render($errorView, array_merge($data, App::$request->getAttributes()));
        } else {
            extract($data, EXTR_OVERWRITE);
            include App::getPath('@framework/views/error.php');
        }

        exit(1);
    }
}
