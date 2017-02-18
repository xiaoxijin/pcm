<?php
define("DS",DIRECTORY_SEPARATOR);
define("ROOT", __DIR__.DS);
error_reporting(E_ALL);
//error_reporting(E_ALL ^ E_NOTICE);

//将当前目录作为Xphp命名空间的初始化根目录
require_once('Loader.php');//加载框架自动加载类库
\Loader::register_autoload();
\Loader::addAllNameSpaceByDir(ROOT);

function pushFailedMsg($msg){
    array_push(\Service::$failed_msg_history,$msg);
    return false;
}

function popFailedMsg(){
    return array_pop(\Service::$failed_msg_history);
}

function cleanPackEnv(){
    \Service::$failed_msg_history=[];
//    \Bootstrap\Service::$service_history=[];
}
/*
 * $path_info : 请求服务路由
 * $params ：act参数， 如果没`有，则默认为寻找服务类名
 */
function service($path_info,$params=''){
    return \Service::getInstance()->run($path_info,$params);
}

function lib($lib_name){
    return \Factory::getInstance()->getProduct("lib",$lib_name);
}

function xphpExceptionHandler($exception) {
    echo $exception->getMessage();
}

// 设置自定义的异常处理函数
set_exception_handler("xphpExceptionHandler");

function xphpErrorExceptionHandler($errno, $errstr, $errfile, $errline ) {

    throw new \ErrorException('SYSTEM_ERROR');
}
//设置自定义的错误处理函数

set_error_handler("xphpErrorExceptionHandler");


class Http extends \Server\Network implements \IFace\Http
{

    public  $get;
    public  $post;
    public  $header;
    private $application;
    public  $http_config =[
        'worker_num' => 16,
        'daemonize' => 0,
        'max_request' => 10000,
        'dispatch_mode' => 1,
        'open_tcp_nodelay' => 1,
        'upload_tmp_dir' => '/data/uploadfiles/',
        'http_parse_post' => true,
    ];
    public function __construct() {
        $config= \Cfg::get('doc');
        parent::__construct($config['host'],$config['port'],'http');
        $this->setCallBack([
            'WorkerStart'=>'onWorkerStart',
            'Request'=>'onRequest',
            ]);
        $this->setConfigure($this->http_config);
        $this->start();
    }

    public function onWorkerStart() {
//        define('APPLICATION_PATH', dirname(__DIR__));
        $config = array(
            "application" => array(
                "directory" => ROOT."Doc".DS.'application'.DS,
            ),
        );
        $this->application = new \Yaf_application($config);
        ob_start();
        $this->application->bootstrap()->run();
        ob_end_clean();
    }

    public function onRequest($request, $response)
    {

        Http\Request::__init($request);
        ob_start();
        try {
            $yaf_request = new \Yaf_Request_Http(
                $_SERVER['request_uri']);

            $this->application->getDispatcher()->dispatch($yaf_request);

            // unset(Yaf_Application::app());
        } catch (\Yaf_Exception $e ) {
            var_dump( $e );
        }
        $result = ob_get_contents();
        ob_end_clean();

        // add Header

        // add cookies

        // set status
        $response->end($result);
        // TODO: Implement onRequest() method.
    }
}
new Http();