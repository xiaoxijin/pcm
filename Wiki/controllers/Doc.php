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

        $description  = '//请检测函数标题描述';
        $descComment  = '//请使用@desc 注释';
        $author='system';
        $returns=[];
        $params=[];
        if (!empty($docCommentArr)) {
            foreach ($docCommentArr as $comment) {
                $comment = trim($comment);


                if (strpos($comment, '@') === false && strpos($comment, '/') === false)
                {
                    //标题描述
                    $this->parseTitle($comment,$title_comment);

                }elseif ($pos = stripos($comment, '@desc'))
                {  //@desc注释
                    $this->parseDes($comment,$pos,$descComment);
                }elseif ($pos = stripos($comment, '@param'))
                {
                    //@param描述
                    $this->parseParam($comment,$pos,$params);
                }elseif ($pos = stripos($comment, '@return'))
                {
                    //@return注释
                    $this->parseReturn($comment,$pos,$returns);
                }
                elseif ($pos = stripos($comment, '@author'))
                {
                    //@return注释
                    $this->parseAuthor($comment,$pos,$author);
                }
            }
        }

        $this->getView()->assign("product_name", "API_DOCS")
            ->assign("service", strtolower($service_name.DS.$act_name))
            ->assign("description", $title_comment)
            ->assign("descComment", $descComment)
            ->assign("params", $params)
            ->assign("returns", $returns)
            ->assign("author", $author);
        return TRUE;
    }

    function parseAuthor($comment,$pos,& $return){

        $return = htmlspecialchars(substr($comment, $pos + 7));
    }

    function parseTitle($comment,& $return){
        $return = substr($comment, strpos($comment, '*') + 1);
    }

    function parseDes($comment,$pos,& $return){
        $return = substr($comment, $pos + 5);
    }

    function parseParam($comment,$pos,& $return){

        $paramsCommentArr = explode(' ', substr($comment, $pos + 6));
        //将数组中的空值过滤掉，同时将需要展示的值返回
        $paramsCommentArr = array_values(array_filter($paramsCommentArr));
        $return[] = $paramsCommentArr;
    }

    function parseReturn($comment,$pos,& $return){

        $returnCommentArr = explode(' ', substr($comment, $pos + 8));
        //将数组中的空值过滤掉，同时将需要展示的值返回
        $returnCommentArr = array_values(array_filter($returnCommentArr));
        if (count($returnCommentArr) < 2) {
            return;
        }
        if (!isset($returnCommentArr[2])) {
            $returnCommentArr[2] = '';	//可选的字段说明
        } else {
            //兼容处理有空格的注释
            $returnCommentArr[2] = implode(' ', array_slice($returnCommentArr, 2));
        }
        $return[] = $returnCommentArr;
    }
}
