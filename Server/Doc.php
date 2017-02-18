<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/2/18
 * Time: 17:39
 */

namespace Server;


class Doc extends Process{

    public $name='CodeReload';
    public function run(){
        parent::run();
        $config= \Cfg::get('doc');
        new Http($config['host'],$config['port']);
    }
}