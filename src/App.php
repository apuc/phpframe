<?php

namespace framework;

use framework\db\Db;
use framework\exceptions\InvalidConfigException;
use framework\views\AbstractViewRenderer;
use framework\web\Request;

class App
{
    public static $config = [];
    public static $aliases = ['@framework' => __DIR__];
    public static $request;

    public function __construct($config = null)
    {
        set_error_handler([$this, 'errorHandler'], E_ERROR | E_WARNING);
        set_exception_handler([$this, 'errorHandler']);

        self::$config = $config;
        self::$request = new Request();
        static::$aliases['@app'] = \dirname($_SERVER['SCRIPT_FILENAME'], 2) . '/app';

        $this->handle();
    }

    private function handle()
    {
        if (array_key_exists('db', self::$config)) {
            $dbConfig = self::$config['db'];
            Db::_init($dbConfig);
        }

        if (array_key_exists('filter', self::$config) && is_callable(self::$config['filter'])) {
            $callable = self::$config['filter'];
            $callable();
        }

        list($controller, $method, $params) = $this->getHandlerAndParams();

        if (!Str::endsWith($controller, 'Controller')) {
            $controller .= 'Controller';
        }
        $class = strpos($controller, 'app\\') === 0 ? $controller : '\\app\\controllers\\' . $controller;

        if (class_exists($class)) {
            $class = new $class;
            if (method_exists($class, $method)) {
                $view = call_user_func_array([$class, $method], $params);
                $this->parseControllerResult($view);
                return;
            }
        }

        throw new \InvalidArgumentException('HTTP handler not found for URL: ' . self::$request->getUri(), 404);
    }

    private function getHandlerAndParams()
    {
        list($handler, $params) = self::route(self::$request->getUri(), self::$config['routes']);

        $data = explode('.', $handler);
        if (count($data) === 2) {
            list($controller, $method) = $data;
        } else {
            $controller = $handler;
            $method = 'handle';
        }

        return [$controller, $method, $params];
    }

    private function parseControllerResult($view)
    {
        if (is_array($view) && count($view) === 2) {
            list($view, $data) = $view;
            foreach ($data as $name => $value) {
                self::$request->set($name, $value);
            }
        }

        if (strpos($view, 'redirect:') === 0) {
            header('Location: ' . substr($view, 9));
            return;
        }

        if ($view !== null) {
            echo self::render($view, self::$request->getAttributes());
        }
    }

    public static function render($view, array $data = [])
    {
        ob_start();

        $viewsPath = self::getConfigValue(['views', 'views_path'], '../app/views');
        $extension = pathinfo($view)['extension'];

        switch ($extension) {
            case 'php':
                extract($data, EXTR_OVERWRITE);
                include $viewsPath . DIRECTORY_SEPARATOR . $view;
                break;
            case 'json':
                if (!headers_sent()) {
                    header('Content-type: application/json');
                }
                echo json_encode($data);
                break;
            default:
                $renderers = (array)self::getConfigValue(['views', 'renderers'], []);
                if (array_key_exists($extension, $renderers)) {
                    $options = $renderers[$extension];
                    $className = '\\' . $options['class'];
                    $cachePath = App::getConfigValue(['views', 'cache_path'], $viewsPath . '/cache');
                    /** @var AbstractViewRenderer $renderer */
                    $renderer = new $className($viewsPath, $cachePath, $options);
                    $renderer->render($view, $data);
                    break;
                }
                throw new \InvalidArgumentException('Illegal view type', 500);
        }

        return ob_get_clean();
    }

    public static function route($input, $routes)
    {
        if (is_array($routes)) {
            foreach ($routes as $key => $value) {
                if (is_array($value)) {
                    list($pattern, $handler) = $value;
                } else {
                    list($pattern, $handler) = [$key, $value];
                }
                if (preg_match('|' . $pattern . '|u', $input, $matches)) {
                    if (is_array($handler)) {
                        return self::route($input, $handler);
                    }
                    array_shift($matches);
                    return [$handler, $matches];
                }
            }
        }

        $parts = explode('/', trim($input, '/'));
        $count = count($parts);
        $controller = $count > 0 ? $parts[0] : 'Site';
        $action = $count > 1 ? $parts[1] : 'index';
        $params = $count > 2 ? array_slice($parts, 2) : [];

        return [$controller, $action, $params];
    }

    public static function loadConfig($name)
    {
        if (!array_key_exists($name, self::$config)) {
            self::$config[$name] = include("../app/config/$name.php");
        }

        return self::$config[$name];
    }

    public static function errorHandler()
    {
        $args = func_get_args();
        if (count($args) === 5) {
            Error::handle($args[0], $args[1], $args[2], $args[3], $args[4]);
        } else {
            Error::handle($args[0]);
        }
    }

    public static function setAlias($alias, $path)
    {
        self::$aliases[$alias] = $path;
    }

    public static function getPath($path)
    {
        if ($path[0] !== '@') {
            return $path;
        }

        $pos = strpos($path, '/');
        if ($pos !== false) {
            $alias = substr($path, 0, $pos);
            if (isset(static::$aliases[$alias])) {
                return static::$aliases[$alias] . substr($path, $pos);
            }
        }

        throw new \InvalidArgumentException('Invalid path alias: ' . $path);
    }

    public static function getConfigValue($key, $default = null, array $config = null)
    {
        $key = (array)$key;
        $config = $config ?: self::$config;

        $lastKey = array_pop($key);
        foreach ($key as $keyPart) {
            $config = array_key_exists($keyPart, $config) ? $config[$keyPart] : null;
            if ($config === null) {
                return $default;
            }
        }

        return array_key_exists($lastKey, $config) ? $config[$lastKey] : $default;
    }

    public static function createObject($type, array $properties = [])
    {
        if (is_string($type)) {
            return static::configure(new $type, $properties);
        }

        if (is_array($type)) {
            $class = $type['class'];
            unset($type['class']);
            return static::configure(new $class, $type);
        }

        throw new InvalidConfigException('Unsupported configuration type: ' . $type);
    }

    public static function configure($object, array $properties = [])
    {
        foreach ($properties as $name => $value) {
            $object->$name = $value;
        }

        return $object;
    }

    private static $classes = [];

    public static function get(string $className): object
    {
        if (!isset(self::$classes[$className])) {
            self::$classes[$className] = new $className;
        }
        return self::$classes[$className];
    }
}
