<?php

namespace Probeyang\Sherlock;

define('DS', DIRECTORY_SEPARATOR);

use Probeyang\Sherlock\Core\Base\App;

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
