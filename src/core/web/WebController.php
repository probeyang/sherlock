<?php

namespace Probeyang\Sherlock\Core\Web;

use \Probeyang\Sherlock\Core\Base\Controller;

class WebController extends Controller {

    private $sherlockBasePath;
    public $module = '';
    public $controller = '';
    public $action = '';
    //basic
    public $suffix = '.php';
    public $appName = 'app';
    public $viewName = 'views';
    public $viewFileName = '';
    public $app;
    public $config;
    public $layout;

    public function __construct() {
        parent::__construct();
        $this->app = \Holmes::app();
        $this->config = $this->app->config;
        $this->layout = $this->layout? : BASE_DIR . '/app/views/layouts/main.php';
    }

    /**
     * 设置视图文件后缀名称，默认为.php，例如可以设置为.html等，也可以设置为空
     * 
     * @param type $suffix
     */
    public function setSuffix($suffix = '.php') {
        $this->suffix = $suffix;
        return $this;
    }

    public function setAppName($app = 'app') {
        $this->appName = $app;
        return $this;
    }

    public function setViewName($view = 'views') {
        $this->viewName = $view;
        return $this;
    }

    public function setViewFileName($path) {
        $this->viewFileName = $path;
        return $this;
    }

    /**
     * 视图渲染函数
     * 
     * @param array/String $view if array,so view is params,params is no useful;if String,that is view path;
     * @param array $params 渲染视图后传给视图的参数数据
     * @return type
     * @throws \Exception
     */
    public function render($view, $params = []) {
        $module = $controller = $action = '';
        //if array,so view args is replace params args;
        if (is_array($view)) {
            extract($view);
            list($module, $controller, $action) = $this->getMca();
        } else if (is_string($view)) {
            extract($params);
            list($module, $controller, $action) = $this->getViews($view);
        } else if (empty($view)) {
            list($module, $controller, $action) = $this->getMca();
        } else {
            throw new \Exception('view only String args', '404');
        }
        $this->getViewFileName($action, $controller, $module);
        $content = $this->viewFileName;
        if ($this->layout === false) {
            return require $content;
        }
        return require $this->layout;
    }

    /**
     * 根据action和controller和module获取视图文件真实路径
     * 
     * @param string $action 方法名
     * @param string $controller 控制器
     * @param string $module 模块名称
     * @return \Probeyang\Sherlock\Core\Web\ViewController
     */
    public function getViewFileName($action, $controller, $module = '') {
        if ($this->viewFileName) {
            return $this->viewFileName;
        }
        $this->sherlockBasePath = baseDir();
        if ($module) {
            $this->viewFileName = $this->sherlockBasePath . '/' . $this->app->appName . '/'
                    . 'modules' . '/' . strtolower($module) . '/' . $this->viewName . '/'
                    . $controller . '/' . $action . $this->suffix;
        } else {
            $this->viewFileName = $this->sherlockBasePath . '/' . $this->app->appName . '/' . $this->viewName
                    . '/' . $controller . '/' . $action . $this->suffix;
        }
        return $this->viewFileName;
    }

    public function getViews($view) {
        $views = explode('/', trim($view, '/'));
        switch (count($views)) {
            case 3:
                $module = $views[0];
                $controller = $views[1];
                $action = $views[2];
                break;
            case 2:
                $module = '';
                $controller = $views[0];
                $action = $views[1];
                break;
            case 1:
                $module = $this->app->module;
                $controller = $this->getController($this->app->controller);
                $action = $view;
                break;
            default:
                $action = $this->app->action;
                $module = $this->app->module;
                $controller = $this->getController($this->app->controller);
                break;
        }
        return [$module, $controller, $action];
    }

    public function getMca() {
        $module = $this->app->module;
        $controller = $this->getController($this->app->controller);
        $action = $this->app->action;
        return [$module, $controller, $action];
    }

    /**
     * 获取module的名称
     */
    public function getModule() {
        $this->module = $this->app->module;
        return $this->module;
    }

    /**
     * 获取action的名称
     */
    public function getAction() {
        $this->action = $this->app->action;
        return $this->action;
    }

    /**
     * 获取controller的名称
     * 
     * @param string $controllerName 含有Controller的控制器名称
     * @return type
     */
    public function getController($controllerName) {
        return strtolower(str_replace('Controller', '', $controllerName));
    }

    /////////////////////////////兼容laravel模式开始////////////////////////////
    public $data = [];
    public $view = '';

    public function make($view = null, $params = []) {
        $module = $controller = $action = '';
        //if array,so view args is replace params args;
        if (is_array($view)) {
            $this->data = $view;
            list($module, $controller, $action) = $this->getMca();
        } else if (is_string($view)) {
            $this->data = $params;
            list($module, $controller, $action) = $this->getViews($view);
        } else if (empty($view)) {
            list($module, $controller, $action) = $this->getMca();
        } else {
            throw new \Exception('view only String args', '404');
        }
        $viewFilePath = $this->getViewFileName($action, $controller, $module);
        if (!is_file($viewFilePath)) {
            throw new \UnexpectedValueException("找不到视图文件！文件地址：" . $viewFilePath);
        }
        $this->view = $viewFilePath;
        return $this;
    }

    public function with($key, $value = null) {
        $this->data[$key] = $value;
        return $this;
    }

    public function __call($method, $params) {
        if (starts_with($method, 'with')) {
            return $this->with(snake_case(substr($method, 4)), $params[0]);
        }
        throw new \BadMethodCallException("方法【$method】不存在！");
    }

    public function __destruct() {
        if ($this->view) {
            extract($this->data);
            require $this->view;
        }
    }

    /////////////////////////////兼容laravel模式结束////////////////////////////
    ////////////////////////////////////////////////////////////
    public function toJson($data) {
        if (is_array($data)) {
            return json_encode($data);
        }
        return $data;
    }

}
