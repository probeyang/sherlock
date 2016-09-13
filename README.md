#sherlock框架使用说明书
#1.不需要你下载本框架代码也不用拉取本框架代码，交给composer来做。
#2.新建一个文件夹，例如holmes，在文件夹中新建composer.json文件
#3.在composer.json文件中添加内容：

  {
      "require": {
          "probeyang/sherlock": "dev-master"
      }
  }
  
#4.在当前文件夹下使用composer命令：

 composer update
 
#如果你觉得下面写的一堆东西乱七八糟不想看，那么可以直接下载附件包，解压就可以使用：
https://github.com/probeyang/sherlock/blob/master/sherlock.zip
下面的讲解就是讲解这个解压包中的各个文件夹，文件的用处和用法的。
 
#5.等待代码拉取完代码后，在composer.json平级目录下面新建几个文件夹：
  （1）app -》 项目代码文件夹，可以改为其他名字，但需要在代码中设置它：
  
      Holmes::app()->appName
      
  （2）config -》 配置文件夹
  
  （3）public -》 项目根目录
  
#6.分别说明三个文件夹内容：
 ##6-1.app文件夹：里面内容为controllers，models，views，modules文件夹。注意点：
 （1）controllers里面的控制器名称为：HomeController.php,类名和文件名一致。web项目的话必须继承自Probeyang\Sherlock\Core\Web\WebController
 
 ##6-2.config文件夹下面文件名是固定的：database.php(数据库配置信息),main.php(网站配置信息),routes.php(路由配置信息)

database.php内容示例：
<?php

return [
    'driver' => 'mysql',
    'host' => 'localhost',
    'database' => 'sherlock',
    'username' => 'root',
    'password' => '',
    'charset' => 'utf8',
    'collation' => 'utf8_general_ci',
    'prefix' => ''
];

main.php内容示例：
<?php

$config = [
    'id' => 'basic',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'modules' => [
        'order' => [
            'namespace' => 'app\modules\order',
        ],
        'goods' => [
            'namespace' => 'app\modules\goods',
        ],
    ],
];
return $config;

routes.php内容示例：
<?php

use Probeyang\Sherlock\Router\Router;

Router::get('hello', function() {
    echo "成功！";
});

//Router::get('(:all)', function($args) {
//    echo '未匹配到路由<br>' . $args;
//});

Router::get('', 'HomeController@index');
Router::get('view', 'HomeController@view');
Router::get('home', 'HomeController@home');
Router::get('index', 'HomeController@index');
Router::get('goods/report/start', 'Goods/ReportController@start');

Router::$error_callback = function() {
    throw new Exception("路由无匹配项 404 Not Found");
};

 ##6-3.public文件夹
网站根目录，放index.php和.htaccess文件和一些静态资源等
index.php内容非常简洁仅仅是这样：
<?php

//定义BASE_DIR
define('BASE_DIR', dirname(__DIR__));
// Autoload 自动载入
require BASE_DIR . '/vendor/autoload.php';
//系统运行
Holmes::app()->run();

就可以开始项目运行了。


#todo：目前还没做到routes的比较好的映射，路由配置很多，后面会想办法强化。谢谢大家支持！
