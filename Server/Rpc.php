<?php
/**
 * Date: 2017/2/14
 * Time: 9:44
 */
namespace Server;
//use Server\Rpc\Http;
//use Server\Rpc\Tcp;

abstract class Rpc extends Network implements \IFace\Rpc
{
    /**
     * debug_server参数
     */
    public $debug_server;
    public $application;
    const SOFT_WARE_SERVER='jcy-http-server';
    const DATE_FORMAT_HTTP = 'D, d-M-Y H:i:s T';
    const CHAR_SET = 'utf-8';
    const HTTP_EOF = "\r\n\r\n";
    const HTTP_HEAD_MAXLEN = 8192; //http头最大长度不得超过2k
    const ST_FINISH = 1; //完成，进入处理流程
    const ST_WAIT   = 2; //等待数据
    const ST_ERROR  = 3; //错误，丢弃此包
    /*--------------以上是debug_server参数------------*/
    use Rpc\Tcp,Rpc\Http;
    public $server_name;
    public $tcp_server;
    public $open_server;
    public $pid_dir;//pid放在当前目录，为了简单实现可以一台服务器上启动多个服务。
    public $task_type = [];
    public $rpc_config;

    function __construct()
    {
        $this->type='http';//使用http服务类型
        $this->rpc_config = \Cfg::get("rpc");
        $this->task_type = $this->rpc_config['tasktype'];
        \Packet::$ret = \Cfg::get("ret");
        \Packet::$task_type = $this->rpc_config['tasktype'];
        $this->server_name =ROOT.$this->rpc_config['name'];
        $this->pid_dir =ROOT;
        parent::__construct($this->rpc_config['host'], $this->rpc_config['http_port'],'http');
        $this->tcp_server = $this->addListener($this->rpc_config['host'], $this->rpc_config['tcp_port'], \SWOOLE_TCP);

        //开启文档调试服务
        /**
         * debug_server初始化服务
         */
        $this->debug_server = $this->addListener($this->rpc_config['host'], $this->rpc_config['debug_port'], \SWOOLE_TCP);
        $mimes = \Loader::importFileByNameSpace('Server','Http/mimes');
        $this->mime_types = array_flip($mimes);
        $this->parser = new Http\Parser;
        $this->http_config = \Cfg::get('wiki');
        $this->deny_dir = array_flip(explode(',', $this->http_config['access']['deny_dir']));
        $this->static_dir = array_flip(explode(',', $this->http_config['access']['static_dir']));
        $this->static_ext = array_flip(explode(',', $this->http_config['access']['static_ext']));
        $this->dynamic_ext = array_flip(explode(',', $this->http_config['access']['dynamic_ext']));
        /*--------------document_root------------*/
        $this->document_root=ROOT.$this->http_config['apps']['directory'];
        $this->setCallBack([
            'Receive'=>'onReceive',
        ],$this->debug_server);

        $this->debug_server->set($this->rpc_config['tcp']);
        /*以上是debug_server服务，可移植*/

        $this->setCallBack([
            'Receive'=>'onRpcReceive',
        ],$this->tcp_server);

        $this->setCallBack([
            'Start'=>'onStart',
            'ManagerStart'=>'onManagerStart',
            'WorkerStart'=>'onWorkerStart',
            'Request'=>'onRpcRequest',
            'WorkerError'=>'onWorkerError',
            'Task'=>'onTask',
            'Finish'=>'onFinish',
        ],$this->server);


        $this->setConfigure($this->rpc_config);
        //invoke the start
        $this->initServer($this->server);
    }

    public function setConfigure(array $external_config = [])
    {
        if (isset($external_config['http'])) {
            parent::setConfigure($external_config['http']);
        }

        if (isset($external_config['tcp'])) {
            $this->tcp_server->set($external_config['tcp']);
        }
    }

    function onStart($server)
    {
        swoole_set_process_name($this->server_name."|Master");
        $master_pid = $server->master_pid;
        $manager_pid = $server->manager_pid;
        echo "MasterPid={$master_pid}\n";
        echo "ManagerPid={$manager_pid}\n";
        echo "Server: start.Swoole version is [" . SWOOLE_VERSION . "]\n";
        \Cache::set("master_pid",$master_pid);
        \Cache::set("manager_pid",$manager_pid);
        file_put_contents("{$this->pid_dir}/Master.pid", $master_pid);
        file_put_contents("{$this->pid_dir}/Manager.pid",$manager_pid);
    }

    //application server first start
    public function onManagerStart($server)
    {
        swoole_set_process_name($this->server_name."|Manager");
    }
    abstract public function initTask($server, $worker_id);
    //worker and task init
    public function onWorkerStart($server, $worker_id)
    {
        $istask = $server->taskworker;
        if (!$istask) {
            //worker
            swoole_set_process_name("{$this->server_name}Worker|{$worker_id}");

            $config = array(
                "application" => array(
                    "directory" => $this->document_root,
                ),
            );
            $this->application = new \Yaf_Application($config);
            ob_start();
            $this->application->bootstrap()->run();
            ob_end_clean();
        } else {
            //task
            swoole_set_process_name("{$this->server_name}Task|{$worker_id}");
            $this->initTask($server, $worker_id);
        }

    }

    public function onTask($serverer, $task_id, $from_id, $data)
    {
//        swoole_set_process_name("doraTask|{$task_id}_{$from_id}|" . $data["api"]["name"] . "");
        switch ($data['type']){
            case $this->task_type['SW_MODE_WAITRESULT_MULTI']:
            case $this->task_type['SW_MODE_NORESULT_MULTI']:
            case $this->task_type['SW_MODE_OPEN_API']:
            case $this->task_type['SW_MODE_DEBUG_API']:
                try {
                    if(!isset($data['api']['name']) || empty($data['api']['name']))
                        throw new \Exception('PARAM_ERR');
                    $ret = $this->doServiceWork($data['api']['name'],$data['api']['params']??'');
                    if($ret)
                        $data["result"] = \Packet::packFormat('OK',$ret);
                    else
                        $data["result"] = \Packet::packFormat('USER_ERROR', $ret,popFailedMsg());
                } catch (\Exception | \ErrorException $e) {
                    $data["result"] = \Packet::packFormat($e->getMessage(),'exception');
                }
                cleanPackEnv();
                break;
            case $this->task_type['SW_MODE_DOC']:
            case $this->task_type['SW_MODE_DEFAULT']:
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

            case $this->task_type['SW_MODE_WAITRESULT_SINGLE']:
                $Packet = \Packet::packFormat('OK', $data["result"]);
                $Packet["guid"] = $guid;
                $Packet = \Packet::packEncode($Packet, $data["protocol"]);

                $serverer->send($fd, $Packet);
                unset($this->taskInfo[$fd][$guid]);

                return true;
                break;

            case $this->task_type['SW_MODE_WAITRESULT_MULTI']:
                if (count($this->taskInfo[$fd][$guid]["taskkey"]) == 0) {
                    $Packet = \Packet::packFormat('OK', $this->taskInfo[$fd][$guid]["result"]);
                    $Packet["guid"] = $guid;
                    $Packet = \Packet::packEncode($Packet, $data["protocol"]);
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

            case $this->task_type['SW_MODE_ASYNCRESULT_SINGLE']:
                $Packet = \Packet::packFormat("OK",$data["result"]);
                $Packet["guid"] = $guid;
                //flag this is result
                $Packet["isresult"] = 1;
                $Packet = \Packet::packEncode($Packet, $data["protocol"]);

                //sys_get_temp_dir
                $serverer->send($fd, $Packet);
                unset($this->taskInfo[$fd][$guid]);

                return true;
                break;
            case $this->task_type['SW_MODE_ASYNCRESULT_MULTI']:
                if (count($this->taskInfo[$fd][$guid]["taskkey"]) == 0) {
                    $Packet = \Packet::packFormat("OK", $this->taskInfo[$fd][$guid]["result"]);
                    $Packet["guid"] = $guid;
                    $Packet["isresult"] = 1;
                    $Packet = \Packet::packEncode($Packet, $data["protocol"]);
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

    public function onWorkerError($server, $worker_id, $worker_pid, $exit_code)
    {
        //using the swoole error log output the error this will output to the swtmp log
//        var_dump("workererror", array($this->taskInfo, $serv, $worker_id, $worker_pid, $exit_code));
    }

    public function __destruct()
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