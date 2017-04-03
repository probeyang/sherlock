<?php

// use \Probeyang\Sherlock\Core\Base\App;
use \Probeyang\Sherlock\ClassName;

class Holmes {

    public static $app;

    public static function app() {
    	$obj = new ClassName();
    	exit;
        if (self::$app) {
            return self::$app;
        }
        self::$app = new App();
        return self::$app;
    }

}
