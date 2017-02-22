<?php
/**
 * 服务入口。
 * @author 肖喜进
 */
class Service
{
    private static $service;
    static $failed_msg_history=[];//执行失败消息历史
    private function __construct(){}
    static function getInstance(){
        if(!self::$service)
            self::$service =  new self();
        return self::$service;
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


