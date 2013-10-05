<?php

class Dispatcher
{
    public static $config = array();

    function __construct()
    {
        set_error_handler('Error::handle', E_ERROR | E_WARNING);
        set_exception_handler('Error::handle');

        $request = new Request();
        $GLOBALS['app']['request'] = $request;

        self::$config = array_merge_recursive(include('../app/config/app.php'), include('../app/config/env.php'));

        if (function_exists('app_filter')) {
            app_filter($request);
        }

        if (isset(self::$config['filters'])) {
            foreach (self::$config['filters'] as $filter) {
                require '../app/' . $filter . '.php';
                /**
                 * @var Filter $filter
                 */
                $filter = new $filter;
                $filter->filter($request); // TODO может сделать один фильтр и вызывать его всегда? или сначала брать конфиг env.php, а в app.php уже иметь возможность оперировать всем чем надо.
            }
        }

        list($controller, $method, $params) = $this->getHandlerAndParams($request->getUri());

        if (substr($controller, -10) !== 'Controller') {
            $controller .= 'Controller';
        }

        require '../app/controllers/' . $controller . '.php';
        $arr = explode('/', $controller);
        $controller = $arr[count($arr) - 1];

        /**
         * @var Controller $controller
         */
        $controller = new $controller($request);

        $this->parseControllerResult(call_user_func_array(array($controller, $method), $params), $request);
    }

    /**
     * Возвращает обработчик по REQUEST_URI и роутинговым правилам
     *
     * @param $uri
     * @return array
     */
    private function getHandlerAndParams($uri)
    {
        list($handler, $params) = self::route($uri, self::$config['routes']);

        $data = explode('.', $handler);
        if (count($data) == 2) {
            list($controller, $method) = $data;
        } else {
            $controller = $handler;
            $method = 'handle';
        }

        return array($controller, $method, $params);
    }

    /**
     * @param $view
     * @param $request
     * @return array
     */
    private function parseControllerResult($view, Request $request)
    {
        if (is_array($view)) {
            if (count($view) == 2) {
                list($view, $data) = $view;
                foreach ($data as $name => $value) {
                    $request->set($name, $value);
                }
            } else if (count($view) == 3) {
                $view = $view[0];
                $request->set($view[1], $view[2]);
            }
        }

        if (substr($view, 0, 9) == 'redirect:') {
            header('Location: ' . substr($view, 9));
            return;
        }

        if ($view != null) {
            self::showView($view, $request->getData());
        }
    }

    /**
     * Отображает указанное представление с переданными параметрами
     *
     * @static
     * @param $view
     * @param array $data
     * @return mixed
     * @throws Exception
     */
    public static function showView($view, $data = array())
    {
        $viewsPath = isset(Dispatcher::$config['views_path']) ? Dispatcher::$config['views_path'] : '../app/views';
        $viewsCachePath = isset(Dispatcher::$config['views_cache_path']) ? Dispatcher::$config['views_cache_path'] : '../app/cache/views';

        $info = pathinfo($view);

        switch ($info['extension']) {

            case 'php':
                extract($data);
                include $viewsPath . '/' . $view;
                break;

            case 'tpl':
                require FRAMEWORK_HOME . '/lib/smarty/Smarty.class.php';
                $smarty = new Smarty();
                $smarty->muteExpectedErrors();
                $smarty->setTemplateDir($viewsPath);
                $smarty->setCompileDir($viewsCachePath);
                $smarty->addPluginsDir(FRAMEWORK_HOME . '/smarty');
                $smarty->addPluginsDir('../app/helpers/smarty');
                $smarty->assign($data);
                $result = $smarty->fetch($view);
                echo $result;
                break;

            case 'json':
                echo json_encode($data);
                break;

            default:
                throw new Exception('Illegal view type', 500);
        }
    }

    /**
     * По роутинговым правилам находит хендлер и возвращает параметры
     *
     * @static
     * @param $input
     * @param $routes
     * @return array
     * @throws Exception
     */
    public static function route($input, $routes)
    {
        if ($routes !== null && is_array($routes)) {
            foreach ($routes as $key => $value) {
                if (is_array($value)) {
                    list($pattern, $hanler) = $value;
                } else {
                    $pattern = $key;
                    $hanler = $value;
                }
                if (preg_match('|' . $pattern . '|u', $input, $matches)) {
                    if (is_array($hanler)) {
                        return self::route($input, $hanler);
                    }
                    array_shift($matches);
                    return array($hanler, $matches);
                }
            }
        }

        throw new Exception('Page not found', 404);
    }

    /**
     * Загрузка конфигурационного файла (должно использоваться отдельными фукнциональными классами или модулями, например, Image, Mail).
     *
     * @static
     * @param $name
     * @return mixed
     */
    public static function loadConfig($name)
    {
        if (!isset(self::$config[$name])) {
            self::$config[$name] = include('../app/config/' . $name . '.php');
        }
        return self::$config[$name];
    }
}

class App extends Dispatcher
{
    // Переход на новый главный класс
}