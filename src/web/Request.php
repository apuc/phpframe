<?php

namespace framework\web;

class Request
{
    public const METHOD_GET = 'GET';
    public const METHOD_POST = 'POST';

    private const FLASH_MESSAGE_ATTRIBUTE = 'request.flash_message';

    private $uri;
    private $attributes = [];

    public function getUri()
    {
        if ($this->uri === null) {
            $data = parse_url(filter_input(INPUT_SERVER, 'REQUEST_URI', FILTER_SANITIZE_STRING));
            $this->uri = $data['path'];
        }

        return $this->uri;
    }

    public function setUri($uri)
    {
        $this->uri = $uri;
    }

    public function getMethod()
    {
        if (isset($_SERVER['REQUEST_METHOD'])) {
            return strtoupper($_SERVER['REQUEST_METHOD']);
        }

        return self::METHOD_GET;
    }

    public function isMethodGet()
    {
        return $this->getMethod() === self::METHOD_GET;
    }

    public function isMethodPost()
    {
        return $this->getMethod() === self::METHOD_POST;
    }

    public function getParameter($name, $default = null)
    {
        return array_key_exists($name, $_REQUEST) ? $_REQUEST[$name] : $default;
    }

    public function getParameters(...$names)
    {
        return array_map(function ($name) {
            return $this->getParameter($name);
        }, $names);
    }

    private $rawBody;

    public function getRawBody()
    {
        if ($this->rawBody === null) {
            $this->rawBody = file_get_contents('php://input');
        }

        return $this->rawBody;
    }

    private $headers;

    public function getHeaders()
    {
        if ($this->headers === null) {
            $this->headers = [];
            if (function_exists('getallheaders')) {
                foreach (getallheaders() as $name => $value) {
                    $this->headers[strtolower($name)] = $value;
                }
            } elseif (function_exists('http_get_request_headers')) {
                foreach (http_get_request_headers() as $name => $value) {
                    $this->headers[strtolower($name)] = $value;
                }
            } else {
                foreach ($_SERVER as $name => $value) {
                    if (strncmp($name, 'HTTP_', 5) === 0) {
                        $name = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))));
                        $this->headers[strtolower($name)] = $value;
                    }
                }
            }
        }

        return $this->headers;
    }

    public function getHeader($name, $default = null)
    {
        return $this->getHeaders()[strtolower($name)] ?? $default;
    }

    public function isAjax()
    {
        return $this->getHeader('X-Requested-With') === 'XMLHttpRequest';
    }

    public function isPjax()
    {
        return $this->isAjax() && $this->getHeader('X-Pjax') !== null;
    }

    public function set($name, $value)
    {
        $this->attributes[$name] = $value;
    }

    public function get($name, $default = null)
    {
        return array_key_exists($name, $this->attributes) ? $this->attributes[$name] : $default;
    }

    public function getAttributes()
    {
        return $this->attributes;
    }

    public function setFlashMessage($message)
    {
        Session::set(self::FLASH_MESSAGE_ATTRIBUTE, $message);
    }

    public function getFlashMessage()
    {
        $message = Session::get(self::FLASH_MESSAGE_ATTRIBUTE);
        if ($message !== null) {
            Session::remove(self::FLASH_MESSAGE_ATTRIBUTE);
        }
        return $message;
    }
}
