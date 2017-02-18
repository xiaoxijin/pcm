<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/2/12
 * Time: 15:00
 */
namespace Service;

class Gym extends \DBService
{

    protected function formatRowData($row){
        $row['logo']=service('img/get',$row['logo_img_id'])['url'];
        unset($row['logo_img_id']);
        unset($row['is_default']);
        return $row;
    }
}

