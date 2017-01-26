<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/1/9
 * Time: 12:53
 */

namespace Xphp\Server;


class CodeReload {

    private $mount_ser_name;
    public function __construct($mount_ser,$mount_ser_name)
    {

        $this->mount_ser_name=$mount_ser_name;
        $process_autoReload = new \swoole_process(array($this, "run"));
        $mount_ser->addProcess($process_autoReload);
    }

    public function run(){
        swoole_set_process_name("{$this->mount_ser_name}Process|AutoReload");
        while (1){
            if($manager_pid = getCache("manager_pid"))
               break;
            else
                sleep(2);
        }
        $ser_auto_reload = new AutoReload($manager_pid);
        foreach (getCfg("modulesMap") as $module_key=>$module_name)
            $ser_auto_reload->watch(SERVER_ROOT.$module_name);
        $ser_auto_reload->addFileType('.php');
        $ser_auto_reload->run();
    }

}