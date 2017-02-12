<?php
namespace Service;
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/2/12
 * Time: 9:20
 */

class Common{
    function timestamp($params){
        return time();
    }

    function getCourseListByDate($params){
        $ret['timestamp'] = $this->timestamp($params);
        $ret['courseList'] = service('course/get');
        return $ret;
    }
}