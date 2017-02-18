<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/2/12
 * Time: 15:29
 */

namespace Service\Gym;

class Banner extends \DBService
{
    public $_table='gym_banner';
    protected function formatRowData($row){

        $row['imgSrc']=service('img/get',$row['img_id'])['url'];
        unset($row['gym_id']);
        unset($row['img_id']);
        unset($row['id']);
        return $row;
    }
}