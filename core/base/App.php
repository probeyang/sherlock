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
    }

    public function includeFiles($folder) {
        foreach (glob("{$folder}/*.php") as $filename) {
            $this->includeFiles[] = $filename;
            require $filename;
        }
    }

}
