<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/2/17
 * Time: 11:10
 */

namespace Server\Rpc;


trait Http
{

    protected $buffer_header = array();
    protected $buffer_maxlen = 65535; //最大POST尺寸，超过将写文件
    protected $mime_types = [];
    protected $parser;
    public $http_config = array();

    /**
     * @var \Swoole\Http\Parser
     */
    protected $static_dir;
    protected $static_root;
    protected $static_ext;
    protected $dynamic_ext;
    protected $document_root;
    protected $deny_dir;
    protected $keepalive = false;
    protected $gzip = false;
    protected $expire = false;

    /**
     * @var \Swoole\Request;
     */
    public $currentRequest;
    /**
     * @var \Swoole\Response;
     */
    public $currentResponse;

    public $requests = array(); //保存请求信息,里面全部是Request对象
    public $dynamic_files = array();
    /**
     * @param $fd
     * @param $http_data
     * @return \Server\Http\Request
     */
    function checkHeader($fd, $http_data)
    {
        //新的连接
        if (!isset($this->requests[$fd]))
        {
            if (!empty($this->buffer_header[$fd]))
            {
                $http_data = $this->buffer_header[$fd].$http_data;
            }
            //HTTP结束符
            $ret = strpos($http_data, self::HTTP_EOF);
            //没有找到EOF，继续等待数据
            if ($ret === false)
            {
                return false;
            }
            else
            {
                $this->buffer_header[$fd] = '';
                $request = new \Server\Http\Request();
                //GET没有body
                list($header, $request->body) = explode(self::HTTP_EOF, $http_data, 2);
                $request->header = $this->parser->parseHeader($header);
                //使用head[0]保存额外的信息
                $request->meta = $request->header[0];
                unset($request->header[0]);
                //保存请求
                $this->requests[$fd] = $request;
                //解析失败
                if ($request->header == false)
                {
//                    $this->log("parseHeader failed. header=".$header);
                    return false;
                }
            }
        }
        //POST请求需要合并数据
        else
        {
            $request = $this->requests[$fd];
            $request->body .= $http_data;
        }
        return $request;
    }

    /**
     * @return int
     */
    function checkPost($request)
    {
        if (isset($request->header['Content-Length']))
        {
            //超过最大尺寸
            if (intval($request->header['Content-Length']) > $this->http_config['server']['post_maxsize'])
            {
                $this->log("checkPost failed. post_data is too long.");
                return self::ST_ERROR;
            }
            //不完整，继续等待数据
            if (intval($request->header['Content-Length']) > strlen($request->body))
            {
                return self::ST_WAIT;
            }
            //长度正确
            else
            {
                return self::ST_FINISH;
            }
        }
        $this->log("checkPost fail. Not have Content-Length.");
        //POST请求没有Content-Length，丢弃此请求
        return self::ST_ERROR;
    }


    function checkData($fd, $http_data)
    {
        if (isset($this->buffer_header[$fd]))
        {
            $http_data = $this->buffer_header[$fd].$http_data;
        }
        //检测头
        $request = $this->checkHeader($fd, $http_data);
        //错误的http头
        if ($request === false)
        {
            $this->buffer_header[$fd] = $http_data;
            //超过最大HTTP头限制了
            if (strlen($http_data) > self::HTTP_HEAD_MAXLEN)
            {
//                $this->log("http header is too long.");
                return self::ST_ERROR;
            }
            else
            {
//                $this->log("wait request data. fd={$fd}");
                return self::ST_WAIT;
            }
        }
        //POST请求需要检测body是否完整
        if ($request->meta['method'] == 'POST')
        {
            return $this->checkPost($request);
        }
        //GET请求直接进入处理流程
        else
        {
            return self::ST_FINISH;
        }
    }

    /**
     * 解析请求
     * @return null
     */
    function parseRequest($request)
    {
        $url_info = parse_url($request->meta['uri']);
        $request->time = time();
        $request->meta['path'] = $url_info['path'];
        if (isset($url_info['fragment'])) $request->meta['fragment'] = $url_info['fragment'];
        if (isset($url_info['query']))
        {
            parse_str($url_info['query'], $request->get);
        }
        //POST请求,有http body
        if ($request->meta['method'] === 'POST')
        {
            $this->parser->parseBody($request);
        }
        //解析Cookies
        if (!empty($request->header['Cookie']))
        {
            $this->parser->parseCookie($request);
        }
    }

    function onReceive($server, $fd, $from_id, $data){

//        $this->server->send($fd, $data);
        $ret = $this->checkData($fd, $data);
        switch($ret)
        {
            //错误的请求
            case self::ST_ERROR;
                $this->server->close($fd);
                return;
            //请求不完整，继续等待
            case self::ST_WAIT:
                return;
            default:
                break;
        }

        //完整的请求
        //开始处理

        /**
         */
        $request = $this->requests[$fd];

        $request->fd = $fd;

        /**
         * Socket连接信息
         */
        $info = $this->server->connection_info($fd);
        $request->server['SWOOLE_CONNECTION_INFO'] = $info;
        $request->remote_ip = $info['remote_ip'];
        $request->remote_port = $info['remote_port'];
        /**
         * Server变量
         */
        $request->server['REQUEST_URI'] = $request->meta['uri'];
        $request->server['REMOTE_ADDR'] = $request->remote_ip;
        $request->server['REMOTE_PORT'] = $request->remote_port;
        $request->server['REQUEST_METHOD'] = $request->meta['method'];
        $request->server['REQUEST_TIME'] = $request->time;
        $request->server['SERVER_PROTOCOL'] = $request->meta['protocol'];
        if (!empty($request->meta['query']))
        {
            $_SERVER['QUERY_STRING'] = $request->meta['query'];
        }

        $this->parseRequest($request);
        $request->setGlobal();
        $this->currentRequest = $request;
        //处理请求，产生response对象
        $response = $this->onRequest($request);
        if ($response and $response instanceof \Server\Http\Response)
        {
            //发送response
            $this->response($request, $response);
        }
    }

    /**
     * 发送响应

     * @return bool
     */
    function response($request,$response)
    {
        if (!isset($response->head['Date']))
        {
            $response->head['Date'] = gmdate("D, d M Y H:i:s T");
        }
        if (!isset($response->head['Connection']))
        {
            //keepalive
            if ($this->keepalive and (isset($request->header['Connection']) and strtolower($request->header['Connection']) == 'keep-alive'))
            {
                $response->head['KeepAlive'] = 'on';
                $response->head['Connection'] = 'keep-alive';
            }
            else
            {
                $response->head['KeepAlive'] = 'off';
                $response->head['Connection'] = 'close';
            }
        }
        //过期命中
        if ($this->expire and $response->http_status == 304)
        {
            $out = $response->getHeader();
            return $this->server->send($request->fd, $out);
        }
        //压缩
        if ($this->gzip)
        {
            if (!empty($request->header['Accept-Encoding']))
            {
                //gzip
                if (strpos($request->header['Accept-Encoding'], 'gzip') !== false)
                {
                    $response->head['Content-Encoding'] = 'gzip';
                    $response->body = gzencode($response->body, $this->http_config['server']['gzip_level']);
                }
                //deflate
                elseif (strpos($request->header['Accept-Encoding'], 'deflate') !== false)
                {
                    $response->head['Content-Encoding'] = 'deflate';
                    $response->body = gzdeflate($response->body, $this->http_config['server']['gzip_level']);
                }
                else
                {
                    $this->log("Unsupported compression type : {$request->header['Accept-Encoding']}.");
                }
            }
        }

        $out = $response->getHeader().$response->body;
        $ret = $this->server->send($request->fd, $out);
        $this->afterResponse($request, $response);
        return $ret;
    }

    function afterResponse($request,$response)
    {
        if (!$this->keepalive or $response->head['Connection'] == 'close')
        {
            $this->server->close($request->fd);
        }
        $request->unsetGlobal();
        //清空request缓存区
        unset($this->requests[$request->fd]);
        unset($request);
        unset($response);
    }

    /**
     * 处理请求
     * @param $request
     */
    function onRequest($request)
    {
        $response = new \Server\Http\Response(self::SOFT_WARE_SERVER,self::CHAR_SET);
        $this->currentResponse = $response;
//        \Swoole::$php->request = $request;
//        \Swoole::$php->response = $response;

        //请求路径
        if ($request->meta['path'][strlen($request->meta['path']) - 1] == '/')
        {
            $request->meta['path'] .= $this->http_config['request']['default_page'];
        }

        if ($this->doStaticRequest($request, $response))
        {
            //pass
        }
        /* 动态脚本 */
        elseif (isset($this->dynamic_ext[$request->ext_name]) or empty($ext_name))
        {
            $this->processDynamic($request, $response);
        }
        else
        {
            $this->httpError(404, $response, "Http Not Found({($request->meta['path']})");
        }
        return $response;
    }

    /**
     * 过滤请求，阻止静止访问的目录，处理静态文件
     * @return bool
     */
    function doStaticRequest($request, $response)
    {
        $path = explode('/', trim($request->meta['path'], '/'));
        //扩展名
        $request->ext_name = $ext_name = \Tool::getFileExt($request->meta['path']);
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
     * 发生了http错误
     * @param                 $code
     * @param string          $content
     */
    function httpError($code, $response, $content = '')
    {
        $response->setHttpStatus($code);
        $response->head['Content-Type'] = 'text/html';
        $response->body = \Server\Http\Response::$HTTP_HEADERS[$code].
            "<p>$content</p><hr><address>" . self::SOFT_WARE_SERVER . " at 0.0.0.0" .
            " Port 9566 </address>";
    }

    /**
     * 处理静态请求

     * @return bool
     */
    function processStatic($request,$response)
    {
        $path = $this->static_root . $request->meta['path'];
        if (is_file($path))
        {
            $read_file = true;
            if ($this->expire)
            {
                $expire = intval($this->http_config['server']['expire_time']);
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
            $ext_name =\Tool::getFileExt($request->meta['path']);
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
        ob_start();
        try {
            $uri = pathinfo(explode('?',$request->meta['uri'],2)[0]);
            $yaf_request = new \Yaf_Request_Http($uri['dirname'].DS.$uri['filename']);
            $this->application
                ->getDispatcher()->dispatch($yaf_request);
            // unset(Yaf_Application::app());
        } catch (\Yaf_Exception  | \Exception | \ErrorException $e) {
            $response->setHttpStatus(500);
            echo "<pre>";
            print_r($e);
            echo '!<br /><h1>' . self::SOFT_WARE_SERVER . '</h1>';
        }
        $response->body = ob_get_contents();
//        $response->body = '123';
        ob_end_clean();
    }


    function loadDynamicFile($file){
        if(!isset($this->dynamic_files[$file]['time']))
        {

            $this->dynamic_files[$file]['time'] = time();
            $this->dynamic_files[$file]['content'] = include $file;
            return;
        }
        clearstatcache();
        $fstat = stat($file);
        //修改时间大于加载时的时间
        if($fstat['mtime'] > $this->dynamic_files[$file]['time'])
        {
            runkit_import($file, RUNKIT_IMPORT_CLASS_METHODS|RUNKIT_IMPORT_OVERRIDE);
            $this->dynamic_files[$file]['time'] = time();
        }else{
            $this->dynamic_files[$file]['content'];
        }

    }

    //http request process
    public function onRpcRequest($request,$response)
    {
        $response->status(200);
        $response->header("Server", self::SOFT_WARE_SERVER);
        $response->header("Date", date(self::DATE_FORMAT_HTTP,time()));
//        $url = strtolower(trim($request->server["request_uri"], "\r\n/ "));

        $path_info = pathinfo(trim(strtolower($request->server["path_info"])));
        $params='';
        $url = strtolower($path_info['filename']);
        if(strtolower($path_info['dirname'])=='/api' ){
            if(($url=='open')
                && $apiName = $request->post['name']??$request->get['name']??''
                    && !empty($apiName)){

                $task["api"]['name'] = trim($apiName, "\r\n/ ");
                $params_string = $request->post['params']??$request->get['params']??'';
                $params = \Parser::actionParams($params_string);
                //todo 根据支付宝的实际情况修复
//                if(isset($request->get)){
//                    $params['CALLBACK_DATA']=$request->get;
//                }elseif(isset($request->post)){
//                    $params['CALLBACK_DATA']=$request->post;
//                }
                $task["api"]['params'] = $params;
                $task['protocol']= "http";

            }elseif($params = $request->post["params"]??$request->get["params"]??''){

                //chenck post error
                $params = json_decode(urldecode($params), true);
                //get the parameter
                //check the parameter need field
                if (!isset($params["guid"]) || !isset($params["api"])) {
                    $response->end(json_encode(\Packet::packFormat('PARAM_ERR')));
                    return;
                }
                //task base info
                $task = array(
                    "guid" => $params["guid"],
                    "fd" => $request->fd,
                    "protocol" => "http",
                );

            }else{
                $response->end(json_encode(\Packet::packFormat('PARAM_ERR')));
            }

            $this->deliveryTask($url,$task,$params,$response);
        }else{
            //todo 实现http服务
            $response->end(json_encode(\Packet::packFormat('PARAM_ERR')));
//            $task['path_info']=$path_info;
//            $task['request'] = $request;
//            $task['response'] = $response;
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

    public function deliveryTask($type,$task,$params,$response){
        switch (strtolower($type)) {
            case "multisync":
                $this->setApiHttpHeader($response);
                $task["type"] = $this->task_type['SW_MODE_WAITRESULT_MULTI'];
                foreach ($params["api"] as $k => $v) {
                    $task["api"] = $v;

                    $taskid = $this->task($task, -1, function ($serv, $task_id, $data) use ($response) {
                        $this->onHttpFinished($serv, $task_id, $data, $response);
                    });
                    $this->taskInfo[$task["fd"]][$task["guid"]]["taskkey"][$taskid] = $k;
                }
                break;
            case "multinoresult":
                $this->setApiHttpHeader($response);
                $task["type"] = $this->task_type['SW_MODE_NORESULT_MULTI'];
                foreach ($params["api"] as $k => $v) {
                    $task["api"] = $v;
                    $this->task($task);
                }
                $pack = \Packet::packFormat('TRANSFER_SUCCESS');
                $pack["guid"] = $task["guid"];
                $response->end(json_encode($pack));
                break;

            case "cmd":
                $task["type"] = $this->task_type['SW_CONTROL_CMD'];
                if ($params["api"]["cmd"]["name"] == "getStat") {
                    $pack = \Packet::packFormat('OK', array("server" => $this->server->stats()));
                    $pack["guid"] = $task["guid"];
                    $response->end(json_encode($pack));
                    return;
                }
                if ($params["api"]["cmd"]["name"] == "reloadTask"){
                    $pack = \Packet::packFormat('OK',array());
                    $this->server->reload(true);
                    $pack["guid"] = $task["guid"];
                    $response->end(json_encode($pack));
                    return;
                }
                break;

            case "open":
                $this->setApiHttpHeader($response);
                $task["type"] = $this->task_type['SW_MODE_OPEN_API'];
                $this->task($task, -1, function ($serv, $task_id, $data) use ($response) {
                    $Packet = \Packet::packEncode($data['result'], $data["protocol"]);
                    $response->end($Packet);
                });
                break;

            case "debug":
                $this->setDebugHttpHeader($response);
                $task["type"] = $this->task_type['SW_MODE_DEBUG_API'];
                $this->task($task, -1, function ($serv, $task_id, $data) use ($response) {
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
                $response->end(json_encode(\Packet::packFormat("unknow task type.未知类型任务", 100002)));
                unset($this->taskInfo[$task["fd"]]);
                return;
//                $task["type"] = DoraConst::SW_MODE_DEFAULT;
//                $this->task($task, -1, function ($serv, $task_id, $context) use ($response) {
//                    $response->end($context);
//                });
//                return;
        }
    }

    //http task finished process
    final public function onHttpFinished($serv, $task_id, $data, $response)
    {
        //fixed the result more than 8k timeout bug
         if (strpos($data, '$$$$$$$$') === 0) {
             $tmp_path = substr($data, 8);
             $data = file_get_contents($tmp_path);
             unlink($tmp_path);

         }
        $data = unserialize($data);
        try{
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
                case $this->task_type['SW_MODE_WAITRESULT_MULTI']:
                    //all task finished

                    if (count($this->taskInfo[$fd][$guid]["taskkey"]) == 0) {
                        $Packet = \Packet::packFormat('OK',$this->taskInfo[$fd][$guid]["result"]);
                        $Packet["guid"] = $guid;
                        $Packet = \Packet::packEncode($Packet, $data["protocol"]);
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
        }catch (\Exception | \ErrorException $e){
            $response->end(json_encode(\Packet::packFormat($e->getMessage(),'exception')));
        }

    }

}