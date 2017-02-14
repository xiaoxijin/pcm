<?php
/**
 * Date: 2017/2/14
 * Time: 9:44
 */

class Rpc implements \IFace\Tcp,\IFace\MultiProcess
{
    /**
     * 网络服务基本配置
     */
    public $server;
    public $name;
    public $host='0.0.0.0';
    public $port='9566';
    public $pid_file;
    private $server_config= array(
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
        'open_length_check' => 1,
        'package_length_type' => 'N',
        'package_length_offset' => 0,
        'package_body_offset' => 4,
        'daemonize' => 0,
    );

    /**
     * 杀死所有进程
     * @param $name
     * @param int $signo
     * @return string
     */
    static function killProcessByName($name, $signo = 9)
    {
        $cmd = 'ps -eaf |grep "' . $name . '" | grep -v "grep"| awk "{print $2}"|xargs kill -'.$signo;
        return exec($cmd);
    }

    function __construct($host,$port,$name='Server')
    {
        $this->server =  new \Swoole\Server($host??$this->host,$port??$this->port);
        $this->setServerName($name);
        $this->setServerCallBack();
        $this->setConfigure();

    }

    function setServerName($name){
        $this->name = $name;
    }
    /**
     * Configuration Server.必须在start之前执行
     *
     * @param array $config
     * @return $this
     */
    function setConfigure(array $external_config=[]){
        $this->server_config=array_merge($this->server_config,$external_config);
    }

    function setServerCallBack(){
        $this->server->on('Start', [$this, 'onStart']);
        $this->server->on('ManagerStart', [$this, 'onManagerStart']);
        $this->server->on('Receive',[$this,'onReceive']);
        $this->server->on('WorkerStart', [$this, 'onWorkerStart']);
        $this->server->on('WorkerError', [$this, 'onWorkerError']);
        $this->server->on('Task', [$this, 'onTask']);
        $this->server->on('Finish', [$this, 'onFinish']);
    }

    function onStart($server)
    {
        // TODO: Implement onStart() method.
        swoole_set_process_name($this->name."|Master");

//        echo "MasterPid={$server->master_pid}\n";
//        echo "ManagerPid={$server->manager_pid}\n";
//        echo "Server: start.Swoole version is [" . SWOOLE_VERSION . "]\n";
//        $this->master_pid = $server->master_pid;
//        $this->manager_pid = $server->manager_pid;
//        file_put_contents("{$this->pid_dir}/Master.pid", $server->master_pid);
//        file_put_contents("{$this->pid_dir}/Manager.pid", $server->manager_pid);
    }

    //application server first start
    public function onManagerStart($server)
    {
        swoole_set_process_name($this->name."|Manager");
//        $this->initStart($server);
    }

    //worker and task init
    final public function onWorkerStart($server, $worker_id)
    {
        $istask = $server->taskworker;
        if (!$istask) {
            //worker
            swoole_set_process_name("{$this->name}Worker|{$worker_id}");
        } else {
            //task
            swoole_set_process_name("{$this->name}Task|{$worker_id}");
            $this->initTask($server, $worker_id);
        }
    }

    public function onTask($server, $task_id, $from_id, $data)
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

    public function onWorkerError($server, $worker_id, $worker_pid, $exit_code)
    {
        //using the swoole error log output the error this will output to the swtmp log
//        var_dump("workererror", array($this->taskInfo, $serv, $worker_id, $worker_pid, $exit_code));
    }

    function onReceive($server, $client_id, $from_id, $data)
    {
        echo "Client:Receive.\n";
        $server->send($client_id, 'Swoole: fd:'.$from_id . ';from_id'.$from_id.';data='.$data);
        // TODO: Implement onReceive() method.
    }


    //task process finished
    function onFinish($server, $task_id, $data)
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

                $server->send($fd, $Packet);
                unset($this->taskInfo[$fd][$guid]);

                return true;
                break;

            case DoraConst::SW_MODE_WAITRESULT_MULTI:
                if (count($this->taskInfo[$fd][$guid]["taskkey"]) == 0) {
                    $Packet = Packet::packFormat('OK', $this->taskInfo[$fd][$guid]["result"]);
                    $Packet["guid"] = $guid;
                    $Packet = Packet::packEncode($Packet, $data["protocol"]);
                    $server->send($fd, $Packet);
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
                $server->send($fd, $Packet);
                unset($this->taskInfo[$fd][$guid]);

                return true;
                break;
            case DoraConst::SW_MODE_ASYNCRESULT_MULTI:
                if (count($this->taskInfo[$fd][$guid]["taskkey"]) == 0) {
                    $Packet = Packet::packFormat("OK", $this->taskInfo[$fd][$guid]["result"]);
                    $Packet["guid"] = $guid;
                    $Packet["isresult"] = 1;
                    $Packet = Packet::packEncode($Packet, $data["protocol"]);
                    $server->send($fd, $Packet);

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