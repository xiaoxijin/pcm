<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/1/9
 * Time: 12:53
 */

namespace Server;


class CodeReload extends Process{

    public $name='CodeReload';
    public function run(){
        parent::run();
        swoole_timer_after(3000, function(){
            $ser_auto_reload = new AutoReload(\Cache::get("manager_pid"));
            $ser_auto_reload->watch(ROOT);
            $ser_auto_reload->addFileType('.php');
            $ser_auto_reload->run();
        });
    }

}