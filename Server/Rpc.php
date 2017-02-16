<?php
/**
 * Date: 2017/2/14
 * Time: 9:44
 */
namespace Server;
abstract class Rpc extends Http implements \IFace\Rpc
{
    public $server_name;
    public $server_config;
    public $pid_dir;
    public $rpc_config = [
        'reactor_num' => 2,
        'worker_num' => 2,
        'task_worker_num' => 4,
        'max_request' => 0, //必须设置为0否则并发任务容易丢,don't change this number
        'task_max_request' => 4000,
    ];

    function __construct()
    {
        $this->type='http';
        $this->server_config = \Cfg::get("server");
//        \Dora\Packet::$ret = getCfg("ret");
        $this->server_name =ROOT.$this->server_config['name'];
        $this->pid_dir =ROOT;
        parent::__construct($this->server_config['host'], $this->server_config['http_port']);
        $this->tcpserver = $this->server->addListener($this->server_config['host'], $this->server_config['tcp_port'], \SWOOLE_TCP);

        $this->setCallBack([
            'Start'=>'onStart',
            'ManagerStart'=>'onManagerStart',
            'WorkerStart'=>'onWorkerStart',
            'WorkerError'=>'onWorkerError',
            'Task'=>'onTask',
            'Finish'=>'onFinish',
        ]);

        $this->setConfigure($this->server_config['setting']);

        $this->pid_dir=$this->pid_dir?$this->pid_dir:__DIR__.DS;
        $this->setConfigure($this->rpc_config);
        //invoke the start
        $this->initServer($this->server);
    }

    function onStart($server)
    {
        swoole_set_process_name($this->server_name."|Master");
        $master_pid = $server->master_pid;
        $manager_pid = $server->manager_pid;
        echo "MasterPid={$master_pid}\n";
        echo "ManagerPid={$manager_pid}\n";
        echo "Server: start.Swoole version is [" . SWOOLE_VERSION . "]\n";
        setCache("master_pid",$master_pid);
        setCache("manager_pid",$manager_pid);
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