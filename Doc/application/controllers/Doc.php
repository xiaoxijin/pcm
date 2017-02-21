<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/2/21
 * Time: 13:01
 */

class DocController extends Yaf_Controller_Abstract {


    public function indexAction() {
        $service_name = $_GET['f'];
        $act_name = $_GET['m'];
        $service_obj = \Factory::getInstance()->getProduct('service',$service_name);

        //获取返回结果
        $rMethod = new ReflectionMethod($service_obj, $act_name);
        $docComment = $rMethod->getDocComment();
        $docCommentArr = explode("\n", $docComment);

        //定义类型
        $typeMaps = array(
            'string'  => '字符串',
            'int'     => '整型',
            'float'   => '浮点型',
            'boolean' => '布尔型',
            'date'    => '日期',
            'array'   => '数组',
            'fixed'   => '固定值',
            'enum'    => '枚举类型',
            'object'  => '对象',
        );
        $params = $typeMaps;
        $description  = '//请检测函数标题描述';
        $descComment  = '//请使用@desc 注释';

        $returns=[];
        if (!empty($docCommentArr)) {
            foreach ($docCommentArr as $comment) {
                $comment = trim($comment);

                //@param描述
                if(stripos($comment, '@param')){
                    $params[] = $comment;
                }

                //标题描述
                if (strpos($comment, '@') === false && strpos($comment, '/') === false) {
                    $description = substr($comment, strpos($comment, '*') + 1);
                    continue;
                }

                //@desc注释
                $pos = stripos($comment, '@desc');
                if ($pos !== false) {
                    $descComment = substr($comment, $pos + 5);
                    continue;
                }

                //@return注释
                $pos = stripos($comment, '@return');
                if ($pos === false) {
                    continue;
                }



                $returnCommentArr = explode(' ', substr($comment, $pos + 8));

                //将数组中的空值过滤掉，同时将需要展示的值返回
                $returnCommentArr = array_values(array_filter($returnCommentArr));
                if (count($returnCommentArr) < 2) {
                    continue;
                }
                if (!isset($returnCommentArr[2])) {
                    $returnCommentArr[2] = '';	//可选的字段说明
                } else {
                    //兼容处理有空格的注释
                    $returnCommentArr[2] = implode(' ', array_slice($returnCommentArr, 2));
                }

                $returns[] = $returnCommentArr;
            }
        }
        $this->getView()->assign("product_name", "API_DOCS")
            ->assign("service", strtolower($service_name.DS.$act_name))
            ->assign("description", $description)
            ->assign("descComment", $descComment)
            ->assign("params", $params)
            ->assign("returns", $returns);
        return TRUE;
    }

}
