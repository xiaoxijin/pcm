<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/2/12
 * Time: 15:00
 */
namespace Service;

class Gym extends \Data\Service
{

    protected function __format_row_data($row){
        $row['logo']=service('img/get',$row['logo_img_id'])['url'];
        unset($row['logo_img_id']);
        unset($row['is_default']);
        return $row;
    }
}

