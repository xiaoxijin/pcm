<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/2/13
 * Time: 10:22
 */

namespace Server;



class Http extends Tcp implements \IFace\Http
{
    const DATE_FORMAT_HTTP='D, d-M-Y H:i:s T';
    public $soft_ware_server='jcy-http-server';
    public $http_config=[
        'upload_tmp_dir' => '/data/uploadFiles/',
        'http_parse_post' => true,
        'open_tcp_nodelay' => 1,
    ];
    function __construct($host='0.0.0.0', $port='9566',$mode = SWOOLE_PROCESS)
    {
        if($this->type=='tcp'){
            parent::__construct($host,$port,$mode);
        }else{
            $this->server= new \Swoole\Http\Server($host,$port);
            $this->setCallBack(['Request'=>'onRequest']);
        }
        $this->setConfigure($this->http_config);
    }



    //http request process
    public function onRequest($request,$response)
    {
        $response->status(200);
        $response->header("Server", $this->soft_ware_server);
        $response->header("Date", date(self::DATE_FORMAT_HTTP,time()));
//        $url = strtolower(trim($request->server["request_uri"], "\r\n/ "));

        $path_info = pathinfo(trim(strtolower($request->server["path_info"])));
        $params='';
        $url = $path_info['filename'];
        if($path_info['dirname']=='/api' ){
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
                return;
            }
        }else{
            $response->end(json_encode(\Packet::packFormat('PARAM_ERR')));
            return;
//            $task['path_info']=$path_info;
//            $task['request'] = $request;
//            $task['response'] = $response;
        }
        $this->deliveryTask($url,$task,$params,$response);
    }


    public function deliveryTask($type,$task,$params,$response){
        switch ($type) {
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
                $pack = Packet::packFormat('TRANSFER_SUCCESS');
                $pack["guid"] = $task["guid"];
                $response->end(json_encode($pack));
                break;

            case "cmd":
                $task["type"] = $this->task_type['SW_CONTROL_CMD'];
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
                $response->end(json_encode(Packet::packFormat("unknow task type.未知类型任务", 100002)));
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
    }
}



