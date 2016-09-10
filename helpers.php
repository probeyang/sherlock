<?php

use \Probeyang\Sherlock\Core\Web\WebController;

if (!function_exists('view')) {

    /**
     * Get the evaluated view contents for the given view.
     *
     * @param  string  $view
     * @param  array   $data
     * @return \Probeyang\Sherlock\Core\Web\ViewController
     */
    function view($view = null, $data = array()) {
        $factory = new WebController();
        return $factory->make($view, $data);
    }

}

if (!function_exists('baseDir')) {

    /**
     * 获取框架的根目录路径
     * 
     * @return type
     */
    function baseDir() {
        if (!defined('BASE_DIR')) {
            $routerDir = DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'probeyang' . DIRECTORY_SEPARATOR . 'sherlock';
            $baseDir = str_replace($routerDir, '', __DIR__);
        } else {
            $baseDir = BASE_DIR;
        }
        return $baseDir;
    }

}