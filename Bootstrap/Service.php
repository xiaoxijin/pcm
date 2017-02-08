<?php


namespace Bootstrap;
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
    public function __construct()
    {
        \Loader::addNameSpace($this->namespace_root_name,$this->path);//注册service的顶级名称空间
    }

    public function run($path_info,$params='')
    {
        $path_info = trim($path_info, " \t\n\r\0\x0B/");
        if (!$path_info)
            throw new \Exception("API_NOT_FOUNT");
        //获取ctl,act,act_params的值
        $route = explode('/', $path_info);
        $route_len = count($route);

        if($route_len == 1){
            $act_name = $route[0];
            $service_obj = $this;
        }else{
            $act_name= $route[$route_len-1];
            unset($route[$route_len-1]);
            $service_name = implode('/',$route);
            $service_obj = \Factory::getInstance()->getProduct('service',$service_name);
        }

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
//    private function getRouteInfo($path_info,$act_name='',$act_params=''){

//        if(is_array($path_info)) {

//            if (!isset($path_info['name']))
//                throw new \Exception('PARAM_ERR');
//
//            $service_name = explode('/',trim($path_info['name'], " \t\n\r\0\x0B/"));
//            $service_name_len = count($service_name);
//
//            if($service_name_len==3) {
//                $route['act_name']=$service_name[$service_name_len-1];
//                unset($service_name[$service_name_len-1]);
//            }else{
//                //必须要为 ucenter/member/get 格式
//                throw new \Exception('PARAM_ERR');
//            }
//            $route['service_name']=implode('/',$service_name);
//            $route['act_params']=$path_info['params']??'';
//            return $route;
//
//        }else{
//
//            $path_info = trim($path_info, " \t\n\r\0\x0B/");
//            $service_name = explode('/',trim($path_info, " \t\n\r\0\x0B/"));
//            $service_name_len = count($service_name);
//
//            if($service_name_len==1 && !is_null(array_pop(self::$service_history)))
//                $service_name =array_pop(self::$service_history)['module_name'].'/'.$path_info;
//            elseif($service_name_len==2)
//                $service_name = $path_info;
//            else{
//                //必须要为 ucenter/member 或者member 格式
//                throw new \Exception('PARAM_ERR');
//            }
//
//            if($act_name!=''){
//                $route['service_name'] = $service_name;
//                $route['act_name'] = $act_name;
//                $route['act_params'] = $act_params;
//                return $route;
//            }
//            return $service_name;
//        }
//    }
    }

}