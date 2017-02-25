<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/2/21
 * Time: 13:01
 */

class DebugController extends Yaf_Controller_Abstract {

    /**
     * 默认动作
     * Yaf支持直接把Yaf_Request_Abstract::getParam()得到的同名参数作为Action的形参
     * 对于如下的例子, 当访问http://yourhost/Test/index/index/index/name/lancelot 的时候, 你就会发现不同
     */
    public function indexAction() {

        $return_values = '';
        $name = $_GET['name']??'';
        $params = trim($_GET['params']??'');
        $service_params='';
        if($params){
            $service_params = \Parser::actionParams($params);
        }
//        var_dump($name);
        if($name){
            try {
                $ret = service($name,$service_params);
                if($ret)
                    $return_values = \Packet::packFormat('OK',$ret);
                else
                    $return_values = \Packet::packFormat('USER_ERROR', $ret,popFailedMsg());
            } catch (\Exception | \ErrorException $e) {
                $return_values = \Packet::packFormat($e->getMessage(),'exception');
            }
            cleanPackEnv();
        }
        $this->getView()->assign("product_name", "API_DOCS")
            ->assign("return", $return_values)
            ->assign("name", $name)
            ->assign("params", $params);

        //4. render by Yaf, 如果这里返回FALSE, Yaf将不会调用自动视图引擎Render模板
        return true;
    }

}
