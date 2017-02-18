<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/2/18
 * Time: 17:44
 */

namespace Server;


class Process
{

    public $name='Process';
    private $mount_ser_name;
    public function __construct($mount_ser,$mount_ser_name)
    {
        $this->mount_ser_name=$mount_ser_name;
        $process_autoReload = new \swoole_process(array($this, "run"),true);
        $mount_ser->addProcess($process_autoReload);
    }

    public function run($worker){
        swoole_set_process_name("{$this->mount_ser_name}Process|{$this->name}");
    }

}