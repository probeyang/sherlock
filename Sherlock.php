<?php

namespace Probeyang\Sherlock;

define('DS', DIRECTORY_SEPARATOR);
define('SHERLOCK_DIR', DS . 'vendor' . DS . 'probeyang' . DS . 'sherlock');
define('SHERLOCK_WEB_DIR', DS . 'vendor' . DS . 'probeyang' . DS . 'sherlock' . DS . 'core' . DS . 'web');

class Sherlock {

    public static $app;
    public $config;

    public static function app() {
        if (self::$app) {
            return self::$app;
        }
        self::$app = new App();
        return self::$app;
    }

}

class App {

    public $appName = 'app';
    public $config;
    public $group;
    public $module;
    public $controller;
    public $action;
    public $modules;
//    public $modelFiles = [];
//    public $controllerFiles = [];
    public $includeFiles = [];

    public function setAction($action) {
        $this->action = $action;
    }

    public function setAppName($appName) {
        $this->appName = $appName;
    }

    public function run($config = []) {
        //load files
        $this->loadFiles($config);
    }

    public function loadFiles($config = []) {
        if (isset($config['modules'])) {
            $this->modules = array_keys($config['modules']);
        }
        $dirArr = $moduleModelsDir = $moduleControllersDir = [];
        $baseDir = str_replace(SHERLOCK_DIR, '', __DIR__);
        if ($this->modules) {
            foreach ($this->modules as $module) {
                $dirArr[] = $baseDir . DS . $this->appName . DS . 'modules' . DS . $module . DS . 'models';
                $dirArr[] = $baseDir . DS . $this->appName . DS . 'modules' . DS . $module . DS . 'controllers';
            }
        }
        $tempDir = [
            $baseDir . DS . $this->appName . DS . 'models',
            $baseDir . DS . $this->appName . DS . 'controllers'
        ];
        $dirArr = array_merge($dirArr, $tempDir);
        foreach ($dirArr as $dir) {
            $this->includeFiles($dir);
        }
    }

////////////////////////////////another method to resolve it///////////////////////////////////////
//    public function loadFiles($config = []) {
//        if (isset($config['modules'])) {
//            $this->modules = array_keys($config['modules']);
//        }
//        $moduleModelsDir = $moduleControllersDir = [];
//        if ($this->modules) {
//            foreach ($this->modules as $module) {
//                $moduleModelsDir = str_replace(SHERLOCK_DIR, '', __DIR__) . DS . $this->appName . DS . 'modules' . DS . $module . DS . 'models';
//                foreach (glob("{$moduleModelsDir}/*.php") as $filename) {
//                    $this->modelFiles[] = $filename;
//                }
//                $moduleControllersDir = str_replace(SHERLOCK_DIR, '', __DIR__) . DS . $this->appName . DS . 'modules' . DS . $module . DS . 'controllers';
//                foreach (glob("{$moduleControllersDir}/*.php") as $filename) {
//                    $this->controllerFiles[] = $filename;
//                }
//            }
//        }
//        $modelFileDir = str_replace(SHERLOCK_DIR, '', __DIR__) . DS . $this->appName . DS . 'models';
//        foreach (glob("{$modelFileDir}/*.php") as $filename) {
//            $this->modelFiles[] = $filename;
//        }
//        $controllerFileDir = str_replace(SHERLOCK_DIR, '', __DIR__) . DS . $this->appName . DS . 'controllers';
//        foreach (glob("{$controllerFileDir}/*.php") as $filename) {
//            $this->controllerFiles[] = $filename;
//        }
//        $this->includeFiles = array_merge($this->modelFiles, $this->controllerFiles);
//        foreach ($this->includeFiles as $file) {
//            require $file;
//        }
//    }

    public function includeFiles($folder) {
        foreach (glob("{$folder}/*.php") as $filename) {
            $this->includeFiles[] = $filename;
            require $filename;
        }
    }

}
