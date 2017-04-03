<?php

namespace Probeyang\Sherlock\Core\Base;

use Illuminate\Database\Capsule\Manager as Capsule;
use \Probeyang\Sherlock\Router\Router;

class App {

    public $appName = 'app';
    public $config;
    public $configFileNames = ['database', 'main', 'routes', 'params'];
    public $group;
    public $module;
    public $controller;
    public $action;
    public $modules;
    public $includeFiles = [];
    public $request = [];
    public $post = [];
    public $get = [];

    public function setAction($action) {
        $this->action = $action;
        return $this;
    }

    public function setAppName($appName) {
        $this->appName = $appName;
        return $this;
    }

    /**
     * 设置默认的配置文件夹中的配置文件名称
     * 
     * @param string/array $configNames
     * @return boolean
     */
    public function setConfigFileNames($configNames = []) {
        if ($configNames) {
            return false;
        }
        if (is_string($configNames)) {
            $configNames = explode(',', $configNames);
            $this->configFileNames = $configNames;
        } else if (is_array($configNames)) {
            $this->configFileNames = $configNames;
        } else {
            return false;
        }
        return $this;
    }

    public function run($config = []) {
        //load config files
        $this->loadConfig();
        if ($config) {
            $this->config = $config;
        }

        //load request args
        $this->loadRequestArgs();
        // Eloquent ORM
        $this->dbHandler();
        //whoops 错误提示
        $this->exceptionHandler();
        //load files
        $this->loadFiles($this->config);
        //路由启动
        Router::dispatch();
    }

    public function loadConfig() {
        $configDir = baseDir() . '/config/';
        foreach ($this->configFileNames as $name) {
            $fileName = $configDir . $name . '.php';
            if (is_file($fileName)) {
                $this->config[$name] = require $fileName;
            }
        }
    }

    public function loadRequestArgs() {
        $this->request();
        $this->post();
        $this->get();
    }

    public function request() {
        $this->request = $this->reverseArgs($_REQUEST);
        return $this->request;
    }

    public function post() {
        $this->post = $this->reverseArgs($_POST);
        return $this->post;
    }

    public function get() {
        $this->get = $this->reverseArgs($_GET);
        return $this->get;
    }

//    function sec(&$array) {
//        //如果是数组，遍历数组，递归调用 
//        if (is_array($array)) {
//            foreach ($array as $k => $v) {
//                $array [$k] = sec($v);
//            }
//        } else if (is_string($array)) {
//            //使用addslashes函数来处理 
//            $array = addslashes($array);
//        } else if (is_numeric($array)) {
//            $array = intval($array);
//        }
//        return $array;
//    }

    public function reverseArgs(&$args) {
        if (is_string($args)) {
            $args = addslashes($args);
        }else if(is_numeric($args)){
            $args = intval($args);
        } else {
            foreach ($args as $key => $arg) {
                $args[$key] = $this->reverseArgs($arg);
            }
        }
        return $args;
    }

    public function dbHandler() {
        // Eloquent ORM
        $capsule = new Capsule();
        $capsule->addConnection($this->config['database']);
        $capsule->bootEloquent();
    }

    public function exceptionHandler() {
        //whoops 错误提示
        $whoops = new \Whoops\Run();
        $whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler());
        $whoops->register();
    }

    public function loadFiles($config = []) {
        if (isset($config['main']['modules'])) {
            $this->modules = array_keys($config['main']['modules']);
        }
        $dirArr = $moduleModelsDir = $moduleControllersDir = [];
        $baseDir = baseDir();
        if ($this->modules) {
            foreach ($this->modules as $module) {
                $dirArr[] = $baseDir . '/' . $this->appName . '/' . 'modules' . '/' . $module . '/' . 'models';
                $dirArr[] = $baseDir . '/' . $this->appName . '/' . 'modules' . '/' . $module . '/' . 'controllers';
            }
        }
        $tempDir = [
            $baseDir . '/' . $this->appName . '/' . 'models',
            $baseDir . '/' . $this->appName . '/' . 'controllers'
        ];
        $dirArr = array_merge($dirArr, $tempDir);
        foreach ($dirArr as $dir) {
            $this->includeFiles($dir);
        }
        //如果存在components文件夹就加载components
        if (file_exists($baseDir . '/components')) {
            $this->includeFiles($baseDir . '/components');
        }
    }

    public function includeFiles($folder) {
        foreach (glob("{$folder}/*.php") as $filename) {
            $this->includeFiles[] = $filename;
            require $filename;
        }
    }

}
