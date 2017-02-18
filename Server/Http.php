<?php

namespace Server;
class Http extends Network implements \IFace\Http
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
    public function __construct($host='0.0.0.0', $port='9566') {
        parent::__construct($host,$port,'http');
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



    public static function getInstance() {
        if (!self::$instance) {
            self::$instance = new HttpServer;
        }
        return self::$instance;
    }
}