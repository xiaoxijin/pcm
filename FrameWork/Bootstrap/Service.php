<?php


namespace Xphp\Bootstrap;
/**
 * 框架入口引导
 * @author 肖喜进
 * Xphp框架系统的核心类，提供一个Xphp对象引用树和基础的调用功能
 * @author     肖喜进
 */
class Service
{

    static $default_module_root;//默认的模块根目录名称

    static $service_history=[];//service 执行历史
    static $failed_msg_history=[];//执行失败消息历史
    public function __construct()
    {
        $config = \Xphp\Data::getInstance()->data("Config");
        $module_config = $config['modulesMap'];//获取MVC模块目录映射配置
        self::$default_module_root =$module_config['default'];//设置默认的模块根目录名称
        foreach ($module_config['map'] as $nameSpace_root=>$url){
            \Loader::addNameSpace($nameSpace_root,$url);//注册MVC app的顶级名称空间
            $config->addModuleConfigPath($url);//注册MVC app的配置文件
        }
    }

    private function getLocalService($service_name){
       return \Xphp\Factory::getInstance()->getProduct('api',$service_name);
    }

    private function requestRemoteService($params){
        return $params;
    }

    private function dispatch($params){

        //获取ctl,act,act_params的值
        list('service_name' => $service_name, 'act_name' => $act_name,'act_params'=>$act_params) =$params;
        if($service_obj = $this->getLocalService($service_name)){

            if (!is_callable([$service_obj, $act_name]))
                return false;
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
            $result = $service_obj->$act_name($act_params);

            if (method_exists($service_obj, '__afterAction'))
                call_user_func(array($service_obj, '__afterAction'));
            //after action

            //class clean
            if (method_exists($service_obj, '__clean'))
                call_user_func(array($service_obj, '__clean'));

            //bootstrap clean
            if (method_exists($this, '__clean'))
                call_user_func(array($this, '__clean'));

        }else{
            throw new \Exception("API_NOT_FOUNT");
//            $result = $this->requestRemoteService($params);
        }
        return $result;
    }

    public function run($path_info,$params=[]){

        if(is_array($route=$this->getRouteInfo($path_info,$params)))
            return $this->dispatch($route);
        else
            return $this->getLocalService($route);

    }


    private function getRouteInfo($path_info,$params=[]){

        /*
         *$task['api']['name'] = 'ucenter/member/getHeadMessage';
         * $task['api']['params'] = array(
         * 'uid'=>63
         *  ); 返回接口数据
         */
        if(is_array($path_info)) {
            if (!isset($path_info['name']))
                return false;

            return $this->parserDataRoute(trim($path_info['name'], " \t\n\r\0\x0B/"),$path_info['params']);
        }

        /*
         *'ucenter/member/getHeadMessage'; 有$params参数
         * member/getHeadMessage 有$params参数
         */
        $path_info = trim($path_info, '/');
        if (count($params)>0){
            return $this->parserDataRoute(trim($path_info, " \t\n\r\0\x0B/"),$params);
        }elseif(($path_len = count(explode('/',$path_info)))<=2){
            /*
            *'ucenter/member/getHeadMessage'; 无$params参数
            * member/getHeadMessage 无$params参数
            */
            if($path_len==1)
                $path_info =self::$current_module_name.'/'.$path_info;
            return $path_info;
        }else{
            return false;
        }

    }

    private function parserDataRoute($route_info,$params){
        $name_arr = explode('/',$route_info);
        $name_arr_len = count($name_arr);
        if($name_arr_len>3)
            return false;
        $route['act_name'] = $name_arr[$name_arr_len-1];
        unset($name_arr[$name_arr_len-1]);

        //根据route信息获取ServiceName
        $service_name =join('/', $name_arr);
        if($name_arr_len==2){
            $service_name=array_pop(self::$service_history)['module_name'].'/'.$service_name;
        }elseif($name_arr_len==1){
            $service_history = array_pop(self::$service_history);
            $service_name=$service_history['module_name'].'/'.$service_history['class_name'];
        }

        $route['service_name'] = $service_name;
        $route['act_params'] = $params;
        return $route;
    }

    public function __init(){
//        array_push(self::$service_history,['module_name'=>self::$current_module_name,'class_name'=>self::$current_class_name]);
    }

    public function __clean(){
//        array_pop(self::$service_history);
    }

}