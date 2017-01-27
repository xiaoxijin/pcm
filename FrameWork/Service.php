<?php


namespace Xphp;
/**
 * 框架入口引导
 * @author 肖喜进
 * Xphp框架系统的核心类，提供一个Xphp对象引用树和基础的调用功能
 * @package    XphpSystem
 * @author     肖喜进
 */
class Service
{

    /**
     * Xphp类的实例
     * @var Xphp
     */
    static $default_module;

    public function __construct()
    {
        $module_config = getCfg("modulesMap");//获取MVC模块目录映射配置
        self::$default_module =$module_config['default'];//设置默认的模块根目录名称
        foreach ($module_config['map'] as $nameSpace_root=>$url){
            \Loader::addNameSpace($nameSpace_root,$url);//注册MVC app的顶级名称空间
            \Xphp\Data::getInstance()->data("Config")->addModuleConfigPath($url);//注册MVC app的配置文件
        }
    }

    public function run($params){

        try{
            //获取ctl,act,act_params的值
            if(list('ctl' => $ctl_name, 'act' => $act_name,'act_params'=>$act_params)  = \Xphp\Route::getRequestInfo($params)){
                $controller = controller($ctl_name);
                //before action
//                $this->callHook(self::HOOK_BEFORE_ACTION);
                //magic method
                if (method_exists($controller, '__beforeAction'))
                {
                    call_user_func(array($controller, '__beforeAction'));
                }
                //do action
                $controller->$act_name($act_params);
                //magic method
                if (method_exists($controller, '__afterAction'))
                {
                    call_user_func(array($controller, '__afterAction'));
                }
                //after action
//                $this->callHook(self::HOOK_AFTER_ACTION);

                return $this->getResult();
            }else
                throw new \Xphp\Exception\NotFound('api class not found.', 100103);
        }catch (Exception $e){
            throw new \Xphp\Exception\NotFound('api class not found.', 100103);
        }
    }
}