<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/2/12
 * Time: 9:20
 */

class Common
{
    protected function convertToArray($delimiter='',$str){
        if($str)
            return explode($delimiter,$str);
        return [];
    }
}