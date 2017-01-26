<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/12/7
 * Time: 17:20
 */

$server[\Xphp\Config::$default_env_name] = array(
    'name'     => exec("pwd")."RPC",
    'pid_dir'  =>exec("pwd"),
    'host'=>'0.0.0.0',
    'http_port'=>9566,
    'tcp_port' =>9567,
    'remote_shell_host'=>'0.0.0.0',
    'remote_shell_port'=>9599,
    'tcp_setting'=>array(
        'reactor_num' => 2, //如果想提高请求接收能力，更改这个，推荐cpu个数x2
        'worker_num' => 2, //包处理进程，根据情况调整数量
        'task_worker_num' => 4,   //实际业务处理进程，根据需要进行调整
        'daemonize' =>1,
        'log_file' => '/tmp/sw_server.log',
        'task_tmpdir' => '/tmp/swtasktmp/',
    ),
    'http_setting'=>array(
        'daemonize' =>1,
    )
);

$server['dev'] = array(
    'http_port'=>9576,
    'tcp_port'=>9577,
    'remote_shell_port'=>9699,
);

$server['test'] = $server['dev'];

$server['local']=array(
    'http_port'=>9586,
    'tcp_port'=>9587,
    'remote_shell_port'=>9899,
    'http_setting'=>array(
        'daemonize' =>0,
    ),
    'tcp_setting'=>array(
        'daemonize' =>0,
    ),
);
return $server;
