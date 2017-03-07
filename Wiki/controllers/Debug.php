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
        $name = $_POST['name']??'';
        $params = $_POST['params']?? [];

        if($name)
            $return_values = \Task::getInstance()->runService($name,$params);

        if(count($params)==0)
            $params = new stdClass();

        if(isset($_SERVER['HTTP_X_REQUESTED_WITH'])){
            echo json_encode([
                'return'=>$return_values,
                'name'=>$name,
                'params'=>$params,
            ]);
            return false;
        }else{
            $this->getView()->assign("product_name", "API_DOCS")
                ->assign("return", $return_values)
                ->assign("name", $name)
                ->assign("params",$params);
            return true;
        }
    }

}
