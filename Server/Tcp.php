<?php
/**
 * User: Administrator
 * Date: 2017/2/14
 * Time: 11:51
 */
namespace Server;
class Tcp  extends Base implements \IFace\Tcp
{

    /**
     * 网络服务基本配置
     */
    public $tcp_config=[
        'open_length_check' => 1,
        'package_length_type' => 'N',
        'package_length_offset' => 0,
        'package_body_offset' => 4,
    ];

    function __construct($host='0.0.0.0', $port='9566',$mode = SWOOLE_PROCESS)
    {
        parent::__construct($host,$port,$mode,SWOOLE_SOCK_TCP);
        $this->setCallBack(['Receive'=>'onReceive']);
        $this->setConfigure($this->tcp_config);
    }
    function close($fd){
        $this->server->close($fd);
    }


    function onReceive($server, $fd, $from_id, $data)
    {
        $requestInfo = \Dora\Packet::packDecode($data);
        if($requestInfo['type']=='tcp') {

            $requestInfo = $requestInfo['data'];
            #decode error
            if ($requestInfo["code"] != 0) {
                $pack["guid"] = $requestInfo["guid"];
                $req = Packet::packEncode($requestInfo);
                $server->send($fd, $req);

                return true;
            } else {
                $requestInfo = $requestInfo["data"];
            }

            #api was not set will fail
            if (!is_array($requestInfo["api"]) && count($requestInfo["api"])) {
                $pack = Packet::packFormat('PARAM_ERR');
                $pack["guid"] = $requestInfo["guid"];
                $pack = Packet::packEncode($pack);
                $server->send($fd, $pack);

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
            $this->deliveryTask($requestInfo["type"], $requestInfo["api"]);
        }
        return true;
    }



    public function onTask($serverer, $task_id, $from_id, $data)
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

    //task process finished
    function onFinish($serverer, $task_id, $data)
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

            case DoraConst::SW_MODE_WAITRESULT_SINGLE:
                $Packet = Packet::packFormat('OK', $data["result"]);
                $Packet["guid"] = $guid;
                $Packet = Packet::packEncode($Packet, $data["protocol"]);

                $serverer->send($fd, $Packet);
                unset($this->taskInfo[$fd][$guid]);

                return true;
                break;

            case DoraConst::SW_MODE_WAITRESULT_MULTI:
                if (count($this->taskInfo[$fd][$guid]["taskkey"]) == 0) {
                    $Packet = Packet::packFormat('OK', $this->taskInfo[$fd][$guid]["result"]);
                    $Packet["guid"] = $guid;
                    $Packet = Packet::packEncode($Packet, $data["protocol"]);
                    $serverer->send($fd, $Packet);
                    //$server->close($fd);
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
                $serverer->send($fd, $Packet);
                unset($this->taskInfo[$fd][$guid]);

                return true;
                break;
            case DoraConst::SW_MODE_ASYNCRESULT_MULTI:
                if (count($this->taskInfo[$fd][$guid]["taskkey"]) == 0) {
                    $Packet = Packet::packFormat("OK", $this->taskInfo[$fd][$guid]["result"]);
                    $Packet["guid"] = $guid;
                    $Packet["isresult"] = 1;
                    $Packet = Packet::packEncode($Packet, $data["protocol"]);
                    $serverer->send($fd, $Packet);

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
}