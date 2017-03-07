<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/2/21
 * Time: 13:01
 */

class ApiController extends Yaf_Controller_Abstract {

    /**
     * 默认动作
     * Yaf支持直接把Yaf_Request_Abstract::getParam()得到的同名参数作为Action的形参
     * 对于如下的例子, 当访问http://yourhost/Test/index/index/index/name/lancelot 的时候, 你就会发现不同
     */
    public function indexAction() {
//        $method = get_class_methods($class);

        $service_name = $_GET['f'];
        $service_obj = \Factory::getInstance()->getProduct('service',$service_name);
        $arrApi = [];
        $ref_service = new ReflectionClass($service_obj);
        foreach ($ref_service->getMethods(ReflectionMethod::IS_PUBLIC) as $mValue) {
            if(substr($mValue->name,0,2)!='__'){
                $rMethod = new Reflectionmethod($service_obj, $mValue->name);
                $title = '//请检测函数注释';
                $desc  = '//请使用@desc 注释';
                $docComment = $rMethod->getDocComment(); //获取评论
                if ($docComment !== false) {
                    $docCommentArr = explode("\n", $docComment);
                    $comment       = trim($docCommentArr[1]);
                    $title         = trim(substr($comment, strpos($comment, '*') + 1));

                    foreach ($docCommentArr as $comment) {
                        $pos = stripos($comment, '@desc');
                        if ($pos !== false) {
                            $desc = substr($comment, $pos + 5);
                        }
                    }
                }
                $service = $service_name . DS . $mValue->name;
                $arrApi[$service] = [
                    'service' => $service,
                    'name' => $mValue->name,
                    'title'   => $title,
                    'desc'    => $desc,
                ];
            }
        }

        $this->getView()->assign("product_name", "API_DOCS")
                        ->assign("methods", $arrApi)
                        ->assign("file", $service_name);

        //4. render by Yaf, 如果这里返回FALSE, Yaf将不会调用自动视图引擎Render模板
        return TRUE;
    }

}
