<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/2/12
 * Time: 12:53
 */
namespace Service;

class Course extends \DB\Service
{

    protected function formatRowData($row){

        $coach_info = service('coach/get',$row['coach_id']);
        $row['courseName'] = $coach_info['name'];
        unset($row['name']);
        unset($row['coach_id']);
        $row['coachName'] = $coach_info['name'];
        $row['coachImg'] = $coach_info['head_img'];

        return $row;
    }
}
