<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/7/24
 * Time: 12:02
 */
namespace Module\Ucenter\Models;
use \Module\Ucenter\Model as Model;


class Data_city extends Model
{
    public $table = 'data_city';
    public $primary='city_id';


    private $city_list;

    public function getCityList(){
        if($this->city_list)
            return $this->city_list;

        $this->city_list = $this->gets();
        return $this->city_list;

    }


    public function getCityNameById($city_id){
        $this->getCityList();
        return $this->city_list[$city_id]['city_name'];
    }


    public function __format_row_index($row){
        return $row['city_id'];
    }
}