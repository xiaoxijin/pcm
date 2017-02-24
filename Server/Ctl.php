<?php
namespace Server;

class Ctl
{
//    static $config_file='/root/fphp.ini';
//    static $install_flag='path';
    static $server;
    static function kill($pid, $signo)
    {
        return posix_kill($pid, $signo);
    }

    static function fork()
    {
        return pcntl_fork();
    }

//    static function unInstall(){
//        if(is_file(self::$config_file))
//            unlink(self::$config_file);
//
//        if(!is_file(self::$config_file))
//        {
//            echo "uninstall rpc server success!";
//        }else {
//            echo "install rpc server failed, the configure is exist!";
//        }
//    }
//
//    static function install(){
//        exec("echo ".self::$install_flag."=".ROOT." > ".self::$config_file);
//        $setting = parse_ini_file(self::$config_file,true);
//        if(isset($setting[self::$install_flag])){
//            echo "install rpc server success!";
//        }else{
//            echo "install rpc server failed, set envpath failed!";
//        }
//    }

    static function start($envName){
        \Cfg::setEnvName($envName);
        self::$server = new Api();
        self::$server->start();
        echo "start rpc server success";
    }

    static function stop(){
        self::$server=null;
        if(exec("ps -axuf | grep ".ROOT." | grep -v grep ")!=""){
            exec(" ps -axuf | grep ".ROOT." | grep -v grep  |awk '{print $2}' | xargs kill -9");
            echo "stop rpc server success \n";
        }else{
            echo "the rpc server already stop.\n";
        }

    }
    
    static function restart($envName){
        self::stop();
        self::start($envName);
    }
}