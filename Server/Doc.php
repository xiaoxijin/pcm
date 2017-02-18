<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/2/18
 * Time: 17:39
 */

namespace Server;


class Doc extends Process{

    public $name='Doc';
    public function run($worker){
//        parent::run($worker);
        $worker->exec('/usr/bin/php', array(__DIR__.DS.'Http.php'));
    }
}