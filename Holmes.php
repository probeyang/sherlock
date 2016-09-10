<?php

//namespace Probeyang\Sherlock;

use Probeyang\Sherlock\Core\Base\App;

class Holmes {

    public static $app;

    public static function app() {
        if (self::$app) {
            return self::$app;
        }
        self::$app = new App();
        return self::$app;
    }

}
