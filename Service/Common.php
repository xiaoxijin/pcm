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
        $ret['courseList'] = service('course/list');
        return $ret;
    }

    function index($params){
//        Gym_Banner
        $ret['timestamp'] = $this->timestamp();
        $gym_info = service('gym/get',['is_default'=>'1']);
        $ret['bannerList'] = service('gym/banner/list',['gym_id'=>$gym_info['id']]);
        unset($gym_info['id']);
        $ret['gymInfoDefault'] = $gym_info;
        $ret['gymList'] = service('gym/list');
        $ret['courseListDefault'] =  service('course/list');
        return $ret;
    }
}