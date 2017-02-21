<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/2/21
 * Time: 13:01
 */

class ListController extends Yaf_Controller_Abstract {

    /**
     * 默认动作
     * Yaf支持直接把Yaf_Request_Abstract::getParam()得到的同名参数作为Action的形参
     * 对于如下的例子, 当访问http://yourhost/Test/index/index/index/name/lancelot 的时候, 你就会发现不同
     */
    public function indexAction(){

        $service_class_dir =ROOT.'Service'.DS;
        $class_dir_name='';
        if (!empty($_GET['d'])) {
            $service_class_dir = $service_class_dir.$_GET['d'].DS;
            $class_dir_name=$_GET['d'].DS;
        }

        $files = array();
        foreach (scandir($service_class_dir) as $file){
            if($file=='.' || $file=='..'){
                continue;
            }
            if (is_dir($service_class_dir.$file) ) {
                $files['type'][] = 'dir';
                $file_name = $class_dir_name.$file;;
            } else {
                $file_info =explode('.',$file,2); //获取后缀
                if ($file_info[1] == 'php') {
                    $file_name = $class_dir_name.$file_info[0];
                    $files['type'][] = 'file';
                }else{
                    continue;
                }
            }
            $files['name'][] = $file_name;
            $files['time'][] = @filemtime($service_class_dir.$file);

        }

        $this->getView()->assign("product_name", "API_DOCS");
        $this->getView()->assign("files", $files);
        //4. render by Yaf, 如果这里返回FALSE, Yaf将不会调用自动视图引擎Render模板
        return TRUE;
    }
}
