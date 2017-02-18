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
    use Rpc\Tcp,Rpc\Http;
    public $date_format_http='D, d-M-Y H:i:s T';
    public $soft_ware_server='jcy-http-server';
    public $server_name;
    public $tcp_server;
    public $debug_server;
    public $open_server;
    public $pid_dir;//pid放在当前目录，为了简单实现可以一台服务器上启动多个服务。
    public $task_type = [];
    public $rpc_config;
//    public $server_config;
    public $server_config = [
        'dispatch_mode' => 3,
        'package_max_length' => 2097152, // 1024 * 1024 * 2,
        'buffer_output_size' => 3145728, //1024 * 1024 * 3,
        'pipe_buffer_size' => 33554432, //1024 * 1024 * 32,
        'open_tcp_nodelay' => 1,
//        'task_ipc_mode'=>3,
//        'message_queue_key'=>0x72000100,
        'heartbeat_check_interval' => 5,
        'heartbeat_idle_time' => 10,
        'open_cpu_affinity' => 1,

        'reactor_num' => 32,//建议设置为CPU核数 x 2
        'worker_num' => 40,
        'task_worker_num' => 20,//生产环境请加大，建议1000

        'max_request' => 0, //必须设置为0，否则会导致并发任务超时,don't change this number
        'task_max_request' => 4000,

        'backlog' => 3000,
        'log_file' => '/tmp/sw_server.log',//swoole 系统日志，任何代码内echo都会在这里输出
        'task_tmpdir' => '/tmp/swtasktmp/',//task 投递内容过长时，会临时保存在这里，请将tmp设置使用内存
        'response_header' => array('Content_Type'=>'application/json; charset=utf-8'),
    ];

    protected $tcpConfig = array(
        'open_length_check' => 1,
        'package_length_type' => 'N',
        'package_length_offset' => 0,
        'package_body_offset' => 4,
        'package_max_length' => 2097152, // 1024 * 1024 * 2,
        'buffer_output_size' => 3145728, //1024 * 1024 * 3,
        'pipe_buffer_size' => 33554432, // 1024 * 1024 * 32,
        'open_tcp_nodelay' => 1,
        'backlog' => 3000,
    );

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
//        $this->debug_server = $this->addListener($this->rpc_config['host'], $this->rpc_config['debug_port'], \SWOOLE_TCP);
//
//        $this->open_server = $this->addListener($this->rpc_config['host'], $this->rpc_config['open_port'], \SWOOLE_TCP);


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

//    public function __initCallBack(){
//
//        $this->server->on('Start', [$this,'onStart']);
//    }

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