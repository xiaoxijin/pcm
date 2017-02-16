<?php
/**
 * Date: 2017/2/14
 * Time: 9:44
 */
namespace Server;
abstract class Rpc extends Http implements \IFace\Rpc
{
    public $server_name;
    public $tcp_server;
    public $server_config;
    public $pid_dir;//pid放在当前目录，为了简单实现可以一台服务器上启动多个服务。
    public $task_type = [];

    function __construct()
    {
        $this->type='http';
        $this->server_config = \Cfg::get("rpc");
        $this->task_type = $this->server_config['tasktype'];
        \Packet::$ret = \Cfg::get("ret");
        $this->server_name =ROOT.$this->server_config['name'];
        $this->pid_dir =ROOT;
        parent::__construct($this->server_config['host'], $this->server_config['http_port']);
        $this->tcp_server = $this->addListener($this->server_config['host'], $this->server_config['tcp_port'], \SWOOLE_TCP);

        $this->setCallBack([
            'Start'=>'onStart',
            'ManagerStart'=>'onManagerStart',
            'WorkerStart'=>'onWorkerStart',
            'Receive'=>'onReceive',
            'WorkerError'=>'onWorkerError',
            'Task'=>'onTask',
            'Finish'=>'onFinish',
        ]);
        $this->setConfigure($this->server_config);
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