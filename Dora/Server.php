<?php
namespace Dora;

/**
 * Class Server
 * https://github.com/xcl3721/Dora-RPC
 * by 蓝天 http://weibo.com/thinkpc
 */
abstract class Server
{


    /*
     * http_server 参数
     */
    protected $http_mimes = array();
    protected $http_server_config = array();
    protected $parser;

    protected $mime_types;
    protected $static_dir;
    protected $static_ext;
    protected $dynamic_ext;
    protected $document_root;
    protected $deny_dir;

    protected $keepalive = false;
    protected $gzip = false;
    protected $expire = false;


    public $currentRequest;

    public $currentResponse;
    const DATE_FORMAT_HTTP = 'D, d-M-Y H:i:s T';
    public $requests = array(); //保存请求信息,里面全部是Request对象
    /*
    * 以上是http_server 参数
    */
    protected $tcpserver = null;
    protected $server = null;
    protected $taskInfo = array();
    protected $reportConfig = array();
    protected $serverIP;
    protected $serverPort;
    protected $monitorProcess = null;
    protected $groupConfig;

    //for extends class overwrite default config
    //用于继承类覆盖默认配置
    protected $externalConfig = array();
    protected $externalHttpConfig = array();

    abstract public function initServer($server);

    public function __construct($ip = "0.0.0.0", $port = 9567, $httpport = 9566, $groupConfig = array(), $reportConfig = array())
    {
        $this->mime_types=\Loader::importFileByNameSpace('Http','/mimes');
        $this->http_server_config = getCfg('http');
        $this->mime_types=$this->mime_types?array_flip($this->mime_types):[];
        $this->parser = new \Http\Parser();

        $this->server = new \swoole_http_server($ip, $httpport);
        $this->tcpserver = $this->server->addListener($ip, $port, \SWOOLE_TCP);
        $httpconfig = array(
            'dispatch_mode' => 3,

            'package_max_length' => 1024 * 1024 * 2,
            'buffer_output_size' => 1024 * 1024 * 3,
            'pipe_buffer_size' => 1024 * 1024 * 32,
            'open_tcp_nodelay' => 1,

            'heartbeat_check_interval' => 5,
            'heartbeat_idle_time' => 10,
            'open_cpu_affinity' => 1,

            'reactor_num' => 2,
            'worker_num' => 2,
            'task_worker_num' => 4,

            'max_request' => 0, //必须设置为0否则并发任务容易丢,don't change this number
            'task_max_request' => 4000,

            'backlog' => 3000,
            'log_file' => '/tmp/sw_server.log',
            'task_tmpdir' => '/tmp/swtasktmp/',

            'daemonize' => 1,
        );

        $tcpconfig = array(
            'open_length_check' => 1,
            'package_length_type' => 'N',
            'package_length_offset' => 0,
            'package_body_offset' => 4,

            'package_max_length' => 1024 * 1024 * 2,
            'buffer_output_size' => 1024 * 1024 * 3,
            'pipe_buffer_size' => 1024 * 1024 * 32,

            'open_tcp_nodelay' => 1,

            'backlog' => 3000,
        );

        //merge config
        if (!empty($this->externalConfig)) {
            $httpconfig = array_merge($httpconfig, $this->externalHttpConfig);
            $tcpconfig = array_merge($tcpconfig, $this->externalConfig);
        }

        //init tcp server
        $this->tcpserver->set($tcpconfig);
        $this->tcpserver->on('Receive', array($this, 'onReceive'));

        //init http server
        $this->server->set($httpconfig);
        $this->server->on('Start', array($this, 'onStart'));
        $this->server->on('ManagerStart', array($this, 'onManagerStart'));

        $this->server->on('Request', array($this, 'onRequest'));
        $this->server->on('WorkerStart', array($this, 'onWorkerStart'));
        $this->server->on('WorkerError', array($this, 'onWorkerError'));
        $this->server->on('Task', array($this, 'onTask'));
        $this->server->on('Finish', array($this, 'onFinish'));


        //invoke the start
        $this->initServer($this->server);

        //store current ip port
        $this->serverIP = $ip;
        $this->serverPort = $port;

        //store current server group
        $this->groupConfig = $groupConfig;
        //if user set the report config will start report
        if (count($reportConfig) > 0) {
            echo "Found Report Config... Start Report Process" . PHP_EOL;
            $this->reportConfig = $reportConfig;
            //use this report the state
            $this->monitorProcess = new \swoole_process(array($this, "monitorReport"));
            $this->server->addProcess($this->monitorProcess);
        }

//        $this->server->start();
    }


    //////////////////////////////server monitor start/////////////////////////////
    //server discovery report
    final public function monitorReport(\swoole_process $process)
    {
        swoole_set_process_name("{$this->server_name}Process|Monitor");

        //file_put_contents("./monitor.pid", getmypid());

        static $_redisObj;

        while (true) {
            //register group and server
            $redisconfig = $this->reportConfig;
            //register this node server info to redis
            foreach ($redisconfig as $redisitem) {

                //validate redis ip and port
                if (trim($redisitem["ip"]) && $redisitem["port"] > 0) {
                    $key = $redisitem["ip"] . "_" . $redisitem["port"];
                    try {
                        if (!isset($_redisObj[$key])) {
                            //if not connect
                            $_redisObj[$key] = new \Redis();
                            $_redisObj[$key]->connect($redisitem["ip"], $redisitem["port"]);
                            if(isset($redisitem["password"]) && !empty($redisitem["password"])){
                                $_redisObj[$key]->auth($redisitem["password"]);
                            }
                        }
                        // 上报的服务器IP
                        $reportServerIP = $this->getReportServerIP();
                        //register this server
                        $_redisObj[$key]->sadd("dora.serverlist", json_encode(array("node" => array("ip" => $reportServerIP, "port" => $this->serverPort), "group" => $this->groupConfig["list"])));
                        //set time out
                        $_redisObj[$key]->set("dora.servertime." . $reportServerIP . "." . $this->serverPort . ".time", time());

                        //echo "Reported Service Discovery:" . $redisitem["ip"] . ":" . $redisitem["port"] . PHP_EOL;

                    } catch (\Exception $ex) {
                        $_redisObj[$key] = null;
                        echo "connect to Service Discovery error:" . $redisitem["ip"] . ":" . $redisitem["port"] . PHP_EOL;
                    }
                }
            }

            sleep(10);
            //sleep 10 sec and report again
        }
    }
    private function  setApiHttpHeader($response){
        //return the json
        $response->header("Content-Type", "application/json; charset=utf-8");
        $response->header("Access-Control-Allow-Origin","*");
        //forever http 200 ,when the error json code decide
        //$response->status(200);

    }

    private function  setDebugHttpHeader($response){
        //return the json
        $response->header("Content-Type", "text/html; charset=utf-8");
//        $response->header("Access-Control-Allow-Origin","*");
        //forever http 200 ,when the error json code decide

    }
    //http request process
    final public function onRequest(\swoole_http_request $request, \swoole_http_response $response)
    {
        $response->status(200);
        $response->header("Server", "jcy-http-server");
        $response->header("Date", date(self::DATE_FORMAT_HTTP,time()));
//        $url = strtolower(trim($request->server["request_uri"], "\r\n/ "));

        $path_info = pathinfo(trim(strtolower($request->server["path_info"])));
        $params='';
        $url='';
        if($path_info['dirname']=='/api' ){
            $url = $path_info['filename'];

            if(($url=='open' || $url=='debug')
                && $apiName = $request->post['name']??$request->get['name']??''
                && !empty($apiName)){

                $task["api"]['name'] = trim($apiName, "\r\n/ ");
                $task["api"]['params'] = $request->post['params']??$request->get['params']??'';
                $task['protocol']= "http";

            }elseif($params = $request->post["params"]??$request->get["params"]??''){

                //chenck post error
                $params = json_decode(urldecode($params), true);
                //get the parameter
                //check the parameter need field
                if (!isset($params["guid"]) || !isset($params["api"])) {
                    $response->end(json_encode(Packet::packFormat('PARAM_ERR')));
                    return;
                }
                //task base info
                $task = array(
                    "guid" => $params["guid"],
                    "fd" => $request->fd,
                    "protocol" => "http",
                );
            }else{
                $response->end(json_encode(Packet::packFormat('PARAM_ERR')));
                return;
            }
        }else{
            $response->end(json_encode(Packet::packFormat('PARAM_ERR')));
            return;
//            $task['path_info']=$path_info;
//            $task['request'] = $request;
//            $task['response'] = $response;
        }

        switch ($url) {
            case "multisync":
                $this->setApiHttpHeader($response);
                $task["type"] = DoraConst::SW_MODE_WAITRESULT_MULTI;
                foreach ($params["api"] as $k => $v) {
                    $task["api"] = $v;
                    $taskid = $this->server->task($task, -1, function ($serv, $task_id, $data) use ($response) {
                        $this->onHttpFinished($serv, $task_id, $data, $response);
                    });
                    $this->taskInfo[$task["fd"]][$task["guid"]]["taskkey"][$taskid] = $k;
                }
                break;
            case "multinoresult":
                $this->setApiHttpHeader($response);
                $task["type"] = DoraConst::SW_MODE_NORESULT_MULTI;
                foreach ($params["api"] as $k => $v) {
                    $task["api"] = $v;
                    $this->server->task($task);
                }
                $pack = Packet::packFormat('TRANSFER_SUCCESS');
                $pack["guid"] = $task["guid"];
                $response->end(json_encode($pack));
                break;

            case "cmd":
                $task["type"] = DoraConst::SW_CONTROL_CMD;
                if ($params["api"]["cmd"]["name"] == "getStat") {
                    $pack = Packet::packFormat('OK', array("server" => $this->server->stats()));
                    $pack["guid"] = $task["guid"];
                    $response->end(json_encode($pack));
                    return;
                }
                if ($params["api"]["cmd"]["name"] == "reloadTask"){
                    $pack = Packet::packFormat('OK',array());
                    $this->server->reload(true);
                    $pack["guid"] = $task["guid"];
                    $response->end(json_encode($pack));
                    return;
                }
                break;

            case "open":
                $this->setApiHttpHeader($response);
                $task["type"] = DoraConst::SW_MODE_OPEN_API;
                $this->server->task($task, -1, function ($serv, $task_id, $data) use ($response) {
                    $Packet = Packet::packEncode($data['result'], $data["protocol"]);
                    $response->end($Packet);
                });
                break;

            case "debug":
                $this->setDebugHttpHeader($response);
                $task["type"] = DoraConst::SW_MODE_DEBUG_API;
                $this->server->task($task, -1, function ($serv, $task_id, $data) use ($response) {
                    ob_start();
                    echo "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\">";
                    echo "<pre>";
                    print_r($data["result"]);
                    $service_data = ob_get_contents();
                    ob_end_clean();
                    $response->end($service_data);
                });
                break;

            default:
                $response->end(json_encode(Packet::packFormat("unknow task type.未知类型任务", 100002)));
                unset($this->taskInfo[$task["fd"]]);
                return;
//                $task["type"] = DoraConst::SW_MODE_DEFAULT;
//                $this->server->task($task, -1, function ($serv, $task_id, $context) use ($response) {
//                    $response->end($context);
//                });
//                return;
        }

    }
    abstract function initStart($server);


    /**
     * 过滤请求，阻止静止访问的目录，处理静态文件

     * @return bool
     */
    function doStaticRequest($request, $response)
    {
        $path = explode('/', trim($request->meta['path'], '/'));
        //扩展名
        $request->ext_name = $ext_name = Swoole\Upload::getFileExt($request->meta['path']);
        /* 检测是否拒绝访问 */
        if (isset($this->deny_dir[$path[0]]))
        {
            $this->httpError(403, $response, "服务器拒绝了您的访问({$request->meta['path']})");
            return true;
        }
        /* 是否静态目录 */
        elseif (isset($this->static_dir[$path[0]]) or isset($this->static_ext[$ext_name]))
        {
            return $this->processStatic($request, $response);
        }
        return false;
    }

    /**
     * 处理静态请求
     * @return bool
     */
    function processStatic($request,$response)
    {
        $path = $this->document_root . '/' . $request->meta['path'];
        if (is_file($path))
        {
            $read_file = true;
            if ($this->expire)
            {
                $expire = intval($this->config['server']['expire_time']);
                $fstat = stat($path);
                //过期控制信息
                if (isset($request->header['If-Modified-Since']))
                {
                    $lastModifiedSince = strtotime($request->header['If-Modified-Since']);
                    if ($lastModifiedSince and $fstat['mtime'] <= $lastModifiedSince)
                    {
                        //不需要读文件了
                        $read_file = false;
                        $response->setHttpStatus(304);
                    }
                }
                else
                {
                    $response->head['Cache-Control'] = "max-age={$expire}";
                    $response->head['Pragma'] = "max-age={$expire}";
                    $response->head['Last-Modified'] = date(self::DATE_FORMAT_HTTP, $fstat['mtime']);
                    $response->head['Expires'] = "max-age={$expire}";
                }
            }
            $ext_name = Swoole\Upload::getFileExt($request->meta['path']);
            if($read_file)
            {
                $response->head['Content-Type'] = $this->mime_types[$ext_name];
                $response->body = file_get_contents($path);
            }
            return true;
        }
        else
        {
            return false;
        }
    }

    /**
     * 处理动态请求
     */
    function processDynamic($request,$response)
    {
        $path = $this->document_root . '/' . $request->meta['path'];
        if (is_file($path))
        {
            $response->head['Content-Type'] = 'text/html';
            ob_start();
            try
            {
                include $path;
                $response->body = ob_get_contents();
            }
            catch (\Exception $e)
            {
                $response->setHttpStatus(500);
                $response->body = $e->getMessage() . '!<br /><h1>' . self::SOFTWARE . '</h1>';
            }
            ob_end_clean();
        }
        else
        {
            $this->httpError(404, $response, "页面不存在({$request->meta['path']})！");
        }
    }

    //application server first start
    final public function onStart(\swoole_server $serv)
    {

        swoole_set_process_name($this->server_name."|Master");

        echo "MasterPid={$serv->master_pid}\n";
        echo "ManagerPid={$serv->manager_pid}\n";
        echo "Server: start.Swoole version is [" . SWOOLE_VERSION . "]\n";
        $this->master_pid = $serv->master_pid;
        $this->manager_pid = $serv->manager_pid;
        file_put_contents("{$this->pid_dir}/Master.pid", $serv->master_pid);
        file_put_contents("{$this->pid_dir}/Manager.pid", $serv->manager_pid);

    }

    //application server first start
    final public function onManagerStart(\swoole_server $serv)
    {
        swoole_set_process_name($this->server_name."|Manager");

        $this->initStart($serv);
    }

    //worker and task init
    final public function onWorkerStart($server, $worker_id)
    {
        $istask = $server->taskworker;
        if (!$istask) {
            //worker
            swoole_set_process_name("{$this->server_name}Worker|{$worker_id}");
        } else {
            //task
            swoole_set_process_name("{$this->server_name}Task|{$worker_id}");
            $this->initTask($server, $worker_id);
        }

    }

    abstract public function initTask($server, $worker_id);

    //tcp request process
    final public function onReceive(\swoole_server $serv, $fd, $from_id, $data)
    {
        $requestInfo = Packet::packDecode($data);

        #decode error
        if ($requestInfo["code"] != 0) {
            $pack["guid"] = $requestInfo["guid"];
            $req = Packet::packEncode($requestInfo);
            $serv->send($fd, $req);

            return true;
        } else {
            $requestInfo = $requestInfo["data"];
        }

        #api was not set will fail
        if (!is_array($requestInfo["api"]) && count($requestInfo["api"])) {
            $pack = Packet::packFormat('PARAM_ERR');
            $pack["guid"] = $requestInfo["guid"];
            $pack = Packet::packEncode($pack);
            $serv->send($fd, $pack);

            return true;
        }
        $guid = $requestInfo["guid"];

        //prepare the task parameter
        $task = array(
            "type" => $requestInfo["type"],
            "guid" => $requestInfo["guid"],
            "fd" => $fd,
            "protocol" => "tcp",
        );

        //different task type process
        switch ($requestInfo["type"]) {

            case DoraConst::SW_MODE_WAITRESULT_SINGLE:
                $task["api"] = $requestInfo["api"]["one"];
                $taskid = $serv->task($task);

                //result with task key
                $this->taskInfo[$fd][$guid]["taskkey"][$taskid] = "one";

                return true;
                break;
            case DoraConst::SW_MODE_NORESULT_SINGLE:
                $task["api"] = $requestInfo["api"]["one"];
                $serv->task($task);

                //return success deploy
                $pack = Packet::packFormat('TRANSFER_SUCCESS');
                $pack["guid"] = $task["guid"];
                $pack = Packet::packEncode($pack);
                $serv->send($fd, $pack);

                return true;

                break;

            case DoraConst::SW_MODE_WAITRESULT_MULTI:
                foreach ($requestInfo["api"] as $k => $v) {
                    $task["api"] = $requestInfo["api"][$k];
                    $taskid = $serv->task($task);
                    $this->taskInfo[$fd][$guid]["taskkey"][$taskid] = $k;
                }

                return true;
                break;
            case DoraConst::SW_MODE_NORESULT_MULTI:
                foreach ($requestInfo["api"] as $k => $v) {
                    $task["api"] = $requestInfo["api"][$k];
                    $serv->task($task);
                }

                $pack = Packet::packFormat('TRANSFER_SUCCESS');
                $pack["guid"] = $task["guid"];
                $pack = Packet::packEncode($pack);

                $serv->send($fd, $pack);

                return true;
                break;
            case DoraConst::SW_CONTROL_CMD:
                if ($requestInfo["api"]["cmd"]["name"] == "getStat") {
                    $pack = Packet::packFormat('OK', array("server" => $serv->stats()));
                    $pack["guid"] = $task["guid"];
                    $pack = Packet::packEncode($pack);
                    $serv->send($fd, $pack);
                    return true;
                }

                if ($requestInfo["api"]["cmd"]["name"] == "reloadTask") {
                    $pack = Packet::packFormat('OK', array("server" => $serv->stats()));
                    $pack["guid"] = $task["guid"];
                    $pack = Packet::packEncode($pack);
                    $serv->send($fd, $pack);
                    $serv->reload(true);
                    return true;
                }

                //no one process
                $pack = Packet::packFormat('UNKNOW_CMD', $this->onRequest());
                $pack = Packet::packEncode($pack);

                $serv->send($fd, $pack);
                unset($this->taskInfo[$fd]);
                break;

            case DoraConst::SW_MODE_ASYNCRESULT_SINGLE:
                $task["api"] = $requestInfo["api"]["one"];
                $taskid = $serv->task($task);
                $this->taskInfo[$fd][$guid]["taskkey"][$taskid] = "one";

                //return success
                $pack = Packet::packFormat('TRANSFER_SUCCESS');
                $pack["guid"] = $task["guid"];
                $pack = Packet::packEncode($pack);
                $serv->send($fd, $pack);

                return true;
                break;
            case DoraConst::SW_MODE_ASYNCRESULT_MULTI:
                foreach ($requestInfo["api"] as $k => $v) {
                    $task["api"] = $requestInfo["api"][$k];
                    $taskid = $serv->task($task);
                    $this->taskInfo[$fd][$guid]["taskkey"][$taskid] = $k;
                }

                //return success
                $pack = Packet::packFormat('TRANSFER_SUCCESS');
                $pack["guid"] = $task["guid"];
                $pack = Packet::packEncode($pack);

                $serv->send($fd, $pack);
                break;
            default:
                $pack = Packet::packFormat('UNKNOW_TASK_TYPE');
                $pack = Packet::packEncode($pack);

                $serv->send($fd, $pack);
                //unset($this->taskInfo[$fd]);

                return true;
        }

        return true;
    }

    final public function onTask($serv, $task_id, $from_id, $data)
    {
//        swoole_set_process_name("doraTask|{$task_id}_{$from_id}|" . $data["api"]["name"] . "");

        switch ($data['type']){
            case DoraConst::SW_MODE_WAITRESULT_MULTI:
            case DoraConst::SW_MODE_NORESULT_MULTI:
            case DoraConst::SW_MODE_OPEN_API:
            case DoraConst::SW_MODE_DEBUG_API:
                try {
                    if(!isset($data['api']['name']) || empty($data['api']['name']))
                        throw new \Exception('PARAM_ERR');
                    $ret = $this->doServiceWork($data['api']['name'],$data['api']['params']??'');
                    if($ret)
                        $data["result"] = Packet::packFormat('OK',$ret);
                    else
                        $data["result"] = Packet::packFormat('USER_ERROR', $ret,popFailedMsg());
                } catch (\Exception | \ErrorException $e) {
                    $data["result"] = Packet::packFormat($e->getMessage(),'exception');
                }
                cleanPackEnv();
                break;
            case DoraConst::SW_MODE_DOC:
            case DoraConst::SW_MODE_DEFAULT:
            default:
                return $this->doDefaultHttpRequest($data['request'],$data['response'],$data['path_info']);
        }
        return $data;
    }

    function doDefaultHttpRequest($request,$response,$path_info)
    {
//        if(!(isset($path_info['extension']) ||
        if(isset($path_info['extension']) && $path_info['extension']=='php'){
//            var_dump($this->http_server_config);
            new \Http\Request($request);

        }else{

        }
        ob_start();
        include ROOT.'Doc'.DS.$path_info['basename'];
        $context = ob_get_contents();
        ob_end_clean();
        return $context;
//        $response = new Swoole\Response;
//        $this->currentResponse = $response;
//        \Swoole::$php->request = $request;
//        \Swoole::$php->response = $response;
//
//        //请求路径
//        if ($request->meta['path'][strlen($request->meta['path']) - 1] == '/')
//        {
//            $request->meta['path'] .= $this->config['request']['default_page'];
//        }
//
//        if ($this->doStaticRequest($request, $response))
//        {
//            //pass
//        }
//        /* 动态脚本 */
//        elseif (isset($this->dynamic_ext[$request->ext_name]) or empty($ext_name))
//        {
//            $this->processDynamic($request, $response);
//        }
//        else
//        {
//            $this->httpError(404, $response, "Http Not Found({($request->meta['path']})");
//        }
//        return $response;
    }


    abstract public function doServiceWork($path_info,$params);
    abstract public function doJcyWork($path_info,$params);

    final public function onWorkerError(\swoole_server $serv, $worker_id, $worker_pid, $exit_code)
    {
        //using the swoole error log output the error this will output to the swtmp log
//        var_dump("workererror", array($this->taskInfo, $serv, $worker_id, $worker_pid, $exit_code));
    }

    /**
     * 获取上报的服务器IP
     * @return string
     */
    protected function getReportServerIP()
    {
        if ($this->serverIP == '0.0.0.0' || $this->serverIP == '127.0.0.1') {
            $serverIps = swoole_get_local_ip();
            $patternArray = array(
                '10\.',
                '172\.1[6-9]\.',
                '172\.2[0-9]\.',
                '172\.31\.',
                '192\.168\.'
            );
            foreach ($serverIps as $serverIp) {
                // 匹配内网IP
                if (preg_match('#^' . implode('|', $patternArray) . '#', $serverIp)) {
                    return $serverIp;
                }
            }
        }
        return $this->serverIP;
    }

    //task process finished
    final public function onFinish($serv, $task_id, $data)
    {
        /*
        //fixed the result more than 8k timeout bug
        if (strpos($data, '$$$$$$$$') === 0) {
            $tmp_path = substr($data, 8);
            $data = file_get_contents($tmp_path);
            unlink($tmp_path);
        }
        $data = unserialize($data);
        */

        $fd = $data["fd"];
        $guid = $data["guid"];

        //if the guid not exists .it's mean the api no need return result
        if (!isset($this->taskInfo[$fd][$guid])) {
            return true;
        }

        //get the api key
        $key = $this->taskInfo[$fd][$guid]["taskkey"][$task_id];

        //save the result
        $this->taskInfo[$fd][$guid]["result"][$key] = $data["result"];

        //remove the used taskid
        unset($this->taskInfo[$fd][$guid]["taskkey"][$task_id]);

        switch ($data["type"]) {

            case DoraConst::SW_MODE_WAITRESULT_SINGLE:
                $Packet = Packet::packFormat('OK', $data["result"]);
                $Packet["guid"] = $guid;
                $Packet = Packet::packEncode($Packet, $data["protocol"]);

                $serv->send($fd, $Packet);
                unset($this->taskInfo[$fd][$guid]);

                return true;
                break;

            case DoraConst::SW_MODE_WAITRESULT_MULTI:
                if (count($this->taskInfo[$fd][$guid]["taskkey"]) == 0) {
                    $Packet = Packet::packFormat('OK', $this->taskInfo[$fd][$guid]["result"]);
                    $Packet["guid"] = $guid;
                    $Packet = Packet::packEncode($Packet, $data["protocol"]);
                    $serv->send($fd, $Packet);
                    //$serv->close($fd);
                    unset($this->taskInfo[$fd][$guid]);

                    return true;
                } else {
                    //not finished
                    //waiting other result
                    return true;
                }
                break;

            case DoraConst::SW_MODE_ASYNCRESULT_SINGLE:
                $Packet = Packet::packFormat("OK",$data["result"]);
                $Packet["guid"] = $guid;
                //flag this is result
                $Packet["isresult"] = 1;
                $Packet = Packet::packEncode($Packet, $data["protocol"]);

                //sys_get_temp_dir
                $serv->send($fd, $Packet);
                unset($this->taskInfo[$fd][$guid]);

                return true;
                break;
            case DoraConst::SW_MODE_ASYNCRESULT_MULTI:
                if (count($this->taskInfo[$fd][$guid]["taskkey"]) == 0) {
                    $Packet = Packet::packFormat("OK", $this->taskInfo[$fd][$guid]["result"]);
                    $Packet["guid"] = $guid;
                    $Packet["isresult"] = 1;
                    $Packet = Packet::packEncode($Packet, $data["protocol"]);
                    $serv->send($fd, $Packet);

                    unset($this->taskInfo[$fd][$guid]);

                    return true;
                } else {
                    //not finished
                    //waiting other result
                    return true;
                }
                break;
            default:

                return true;
                break;
        }

    }

    //http task finished process
    final public function onHttpFinished($serv, $task_id, $data, $response)
    {
        $fd = $data["fd"];
        $guid = $data["guid"];

        //if the guid not exists .it's mean the api no need return result
        if (!isset($this->taskInfo[$fd][$guid])) {
            return true;
        }

        //get the api key
        $key = $this->taskInfo[$fd][$guid]["taskkey"][$task_id];

        //save the result
        $this->taskInfo[$fd][$guid]["result"][$key] = $data["result"];

        //remove the used taskid
        unset($this->taskInfo[$fd][$guid]["taskkey"][$task_id]);

        switch ($data["type"]) {
            case DoraConst::SW_MODE_WAITRESULT_MULTI:
                //all task finished
                if (count($this->taskInfo[$fd][$guid]["taskkey"]) == 0) {

                    $Packet = Packet::packFormat('OK',$this->taskInfo[$fd][$guid]["result"]);
                    $Packet["guid"] = $guid;
                    $Packet = Packet::packEncode($Packet, $data["protocol"]);
                    unset($this->taskInfo[$fd][$guid]);
                    $response->end($Packet);
                    
                    return true;
                } else {
                    //not finished
                    //waiting other result
                    return true;
                }
                break;
            default:

                return true;
                break;
        }
    }

    final public function __destruct()
    {
        echo "Server Was Shutdown..." . PHP_EOL;
        //shutdown
        $this->server->shutdown();
        /*
        //fixed the process still running bug
        if ($this->monitorProcess != null) {
            $monitorPid = trim(file_get_contents("./monitor.pid"));
            \swoole_process::kill($monitorPid, SIGKILL);
        }
        */
    }

}
