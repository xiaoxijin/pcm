<?php
namespace Service;
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/2/12
 * Time: 9:20
 */

class Common{
    function timestamp($params=''){
        return time();
    }

    function getCourseListByDate($params){
        $ret['timestamp']  = $this->timestamp();
        $ret['courseList'] = service('course/get');
        return $ret;
    }

    function index($params){
        $ret['timestamp'] = $this->timestamp();
        $ret['gymInfoDefault'] = service('gym/get',['is_default'=>'1']);
        $ret['courseListDefault'] =  service('course/get');
        return $ret;
    }
}