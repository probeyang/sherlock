<?php

namespace Probeyang\Sherlock\Router;

class Router {

    public static $routes = [];
    public static $callbacks = [];
    public static $methods = [];
    public static $error_callback;
    public static $stop = true;
    public static $routed = false; //判断是否路由成功，如果不成功，则显示错误页面。但如果不想显示错误页面，可以人为设置它为成功。也可以设置stop为true
    public static $patterns = [
        ':any' => '[^/]+',
        ':num' => '[0-9]+',
        ':all' => '.*',
    ];

    public static function __callStatic($method, $args) {
        self::_request($method, $args);
    }

    public static function get($func, $args) {
        $data = [$func, $args];
        self::_request(__FUNCTION__, $data);
    }

    public static function post($func, $args) {
        $data = [$func, $args];
        self::_request(__FUNCTION__, $data);
    }

    public static function stop($stop = TRUE) {
        self::$stop = $stop;
    }

    public static function routed($routed = TRUE) {
        self::$routed = $routed;
    }

    public static function dispatch() {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = parse_url($_SERVER['PHP_SELF'], PHP_URL_PATH);
        $uri = str_replace('/index.php', '/', $uri);
        self::$routes = str_replace('//', '/', self::$routes);
        if (in_array($uri, self::$routes)) {
            $routeIndex = current(array_keys(self::$routes, $uri));
            if (self::$methods[$routeIndex] == $method || self::$methods[$routeIndex] == 'ANY' || self::$methods[$routeIndex] == 'ALL') {
                self::$routed = true;
                if (is_object(self::$callbacks[$routeIndex])) {
                    call_user_func(self::$callbacks[$routeIndex]);
                } else {
                    //cut for module
                    list($group, $module, $controllerAction) = self::_groupAndModuleAndController($routeIndex);
                    if ($controllerAction) {
                        self::_action($controllerAction, $module, $group);
                    }
                }
            }
        } else {
            list($module, $controller, $action) = explode('/', trim($uri, '/'));
            if (self::_isExist($controller, $module)) {
                //----------兼容一些不配置routes信息的，也进行路由。------开始----------
                self::$routed = true;
                $controllerAction = ucfirst($controller) . 'Controller@' . $action;
                self::_action($controllerAction, $module);
                //----------兼容一些不配置routes信息的，也进行路由。------结束----------
            } else {
                //正则匹配
                $searchs = array_keys(self::$patterns);
                $replaces = array_values(self::$patterns);
                $routeIndex = 0;
                foreach (self::$routes as $route) {
                    if (strpos($route, ':') !== false) {
                        $route = str_replace($searchs, $replaces, $route);
                    }
                    if (preg_match('#^' . $route . '$#', $uri, $matched)) {
                        if (self::$methods[$routeIndex] == $method || self::$methods[$routeIndex] == 'ANY' || self::$methods[$routeIndex] == 'ALL') {
                            self::$routed = true;
                            array_shift($matched);
                            if (is_object(self::$callbacks[$routeIndex])) {
                                call_user_func_array(self::$callbacks[$routeIndex], $matched);
                            } else {
                                list($group, $module, $controllerAction) = self::_groupAndModuleAndController($routeIndex);
                                if ($controllerAction) {
                                    self::_action($controllerAction, $module, $group, $matched);
                                }
                            }
                            if (self::$stop) {
                                return;
                            }
                        }
                    }
                    $routeIndex++;
                }
            }
            
        }
        if (self::$stop) {
            return;
        }
        if (!self::$routed) {
            self::_error();
        }
    }

    private static function _isExist($controller, $module = '') {
        $app = \Holmes::app();
        $baseDir = baseDir();
        if ($module) {
            $fileName = $baseDir . '/' . $app->appName . '/' . 'modules' . '/' . $module . '/' . 'controllers/' . ucfirst($controller) . 'Controller.php';
            if (is_file($fileName)) {
                return true;
            }
            return false;
        }
        $fileName = $baseDir . '/' . $app->appName . 'controllers/' . ucfirst($controller) . 'Controller.php';
        if (is_file($fileName)) {
            return true;
        }
        return false;
    }

    private static function _groupAndModuleAndController($routeKey) {
        $callbackArr = explode('/', self::$callbacks[$routeKey]);
        $groupCount = count($callbackArr);
        $group = $module = $controllerAction = '';
        switch ($groupCount) {
            case 1:
                $controllerAction = array_shift($callbackArr);
                break;
            case 2:
                $module = ucfirst(array_shift($callbackArr));
                $controllerAction = array_shift($callbackArr);
                break;
            case 3:
                $group = ucfirst(array_shift($callbackArr));
                $module = ucfirst(array_shift($callbackArr));
                $controllerAction = array_shift($callbackArr);
                break;
        }
        return [$group, $module, $controllerAction];
    }

    private static function _action($controllerAction, $module = '', $group = '', $matched = []) {
        $segments = explode('@', $controllerAction);
        $action = end($segments);
        //给sherlock的全局app对象注入数据
        $app = \Holmes::app();
        if ($group && $module) {
            $controllerClass = '\\' . $group . '\\' . $module . '\\' . $segments[0];
            //获取控制器命名空间地址
            $namespaceController = ucfirst($app->appName) . '\\' . ucfirst($group) . '\\' . ucfirst($module) . '\\Controllers\\' . $controllerClass;
        } elseif (!$group && $module) {
            $controllerClass = $segments[0];
            //获取控制器命名空间地址
            $namespaceController = ucfirst($app->appName) . '\\Modules\\' . ucfirst($module) . '\\Controllers\\' . $controllerClass;
        } else {
            $controllerClass = $segments[0];
            //获取控制器命名空间地址
            $namespaceController = ucfirst($app->appName) . '\\' . 'Controllers\\' . $controllerClass;
        }
        $app->action = $action;
        $app->module = $module;
        $app->controller = $controllerClass;
        //实例化控制器
        $controller = new $namespaceController();
        if (method_exists($controller, $action) && is_callable([$controller, $action])) {
            $matched ? $controller->$action($matched) : $controller->$action();
        }
    }

    public static function error($callback) {
        self::$error_callback = $callback;
    }

    private static function _error() {
        if (!self::$error_callback) {
            self::$error_callback = function() {
                header($_SERVER['SERVER_PROTOCOL'] . " 404 Not Found");
                echo '404';
            };
        } else {
            if (is_string(self::$error_callback)) {
                self::get($_SERVER['REQUEST_URI'], self::$error_callback);
                self::$error_callback = null;
                self::dispatch();
                return;
            }
        }
        call_user_func(self::$error_callback);
    }

    private static function _request($method, $args) {
//        $uri = dirname($_SERVER['PHP_SELF']) . '/' . $args[0]; // dirname($_SERVER['PHP_SELF'])->\,/index.php
        $uri = dirname($_SERVER['SCRIPT_NAME']) . '/' . $args[0]; // dirname($_SERVER['PHP_SELF'])->\,/index.php
        $uri = str_replace('/index.php', '', $uri);
        $uri = ($uri == '\/') ? '/' : $uri;
//        var_dump($uri,$_SERVER['SCRIPT_NAME'],$method,$args);exit;
        array_push(self::$routes, $uri);
        array_push(self::$methods, strtoupper($method));
        array_push(self::$callbacks, $args['1']);
    }

}
