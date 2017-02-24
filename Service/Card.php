<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/2/12
 * Time: 15:00
 */
namespace Service;

class Card extends \DB\Service
{

    protected function formatRowData($row){
        $row['cardFaceUrl']=service('img/get',$row['card_face_img_id'])['url'];
        unset($row['card_face_img_id']);
//        unset($row['is_default']);
        return $row;
    }
}

