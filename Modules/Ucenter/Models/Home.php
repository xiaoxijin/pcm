<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/7/24
 * Time: 12:02
 */
namespace Module\Ucenter\Models;
use \Module\Ucenter\Model as Model;


class Home extends Model
{
    public $table = 'home';
    public $primary='id';


    private $home_list;

    public function getHomeList(){
        if($this->home_list)
            return $this->home_list;

        $this->home_list = $this->gets();
        return $this->home_list;

    }


    public function getHomeNameById($home_id){
        $this->getHomeList();
        return $this->home_list[$home_id]['name'];
    }


    public function __format_row_index($row){
        return $row['id'];
    }
}