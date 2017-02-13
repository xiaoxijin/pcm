<?php



/**
 * 框架入口引导
 * @author 肖喜进
 * Xphp框架系统的核心类，提供一个Xphp对象引用树和基础的调用功能
 * @author     肖喜进
 */
class Service
{

    private $namespace_root_name='Service';//默认的服务根目录名称
    private $path=ROOT.'Service'.DS;//默认的服务根目录路径

//    static $service_history=[];//service 执行历史
    static $failed_msg_history=[];//执行失败消息历史
    public function __construct(){
        \Loader::addNameSpace($this->namespace_root_name,$this->path);//注册service的顶级名称空间}
    }

    public function run($uri,$params='')
    {
        $uri = trim($uri, " \t\n\r\0\x0B/\\");
        if (!$uri || Validate::notService($uri))
            throw new \Exception("SERVICE_URI_INVALID");
        //获取ctl,act,act_params的值
        $route = explode('/', $uri);
        $route_len = count($route);

        if($route_len == 1){
            $act_name = $route[0];
            $service_name = 'Common';
        }else{
            $act_name= $route[$route_len-1];
            unset($route[$route_len-1]);
            $service_name = implode('/',$route);

        }
        $service_obj = \Factory::getInstance()->getProduct('service',$service_name);
        if (!is_callable([$service_obj, $act_name]))
            throw new \Exception("API_NOT_FOUNT");
        //bootstrap init
        if (method_exists($this, '__init'))
            call_user_func(array($this, '__init'));

        //class init
        if (method_exists($service_obj, '__init'))
            call_user_func(array($service_obj, '__init'));

        //before action
        if (method_exists($service_obj, '__beforeAction'))
            call_user_func(array($service_obj, '__beforeAction'));


        //do action
        $result = $service_obj->$act_name($params);

        if (method_exists($service_obj, '__afterAction'))
            call_user_func(array($service_obj, '__afterAction'));
        //after action

        //class clean
        if (method_exists($service_obj, '__clean'))
            call_user_func(array($service_obj, '__clean'));

        //bootstrap clean
        if (method_exists($this, '__clean'))
            call_user_func(array($this, '__clean'));
        return $result;
    }
}

function pushFailedMsg($msg){
    array_push(\Service::$failed_msg_history,$msg);
}

function popFailedMsg(){
    return array_pop(\Service::$failed_msg_history);
}

function cleanPackEnv(){
    \Service::$failed_msg_history=[];
//    \Bootstrap\Service::$service_history=[];
}

