<?php

namespace Probeyang\Sherlock\Core\Web;

use Probeyang\Sherlock\Core\Base\Controller;

class WebController extends Controller{

    public function render($view,$params = []){
        extract($params);
        return require BASE_URL . '/view/' . $view . '.html';
        //return require 'D:/wamp/www/sherlock/view/hello/view.html';
    }

    public function toJson($data){
        if(is_array($data)){
            return json_encode($data);
        }
        return $data;
    }
}
