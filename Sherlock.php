<?php

namespace Probeyang\Sherlock;

class Sherlock {

    public static $app;

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
    public $group;
    public $module;
    public $controller;
    public $action;

    public function setAction($action) {
        $this->action = $action;
    }

    public function setAppName($appName) {
        $this->appName = $appName;
    }

}