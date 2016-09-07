<?php

use \Probeyang\Sherlock\Core\Web\ViewController;

if (!function_exists('view')) {

    /**
     * Get the evaluated view contents for the given view.
     *
     * @param  string  $view
     * @param  array   $data
     * @return \Probeyang\Sherlock\Core\Web\ViewController
     */
    function view($view = null, $data = array()) {
        $factory = new ViewController();
        return $factory->make($view, $data);
    }

}

