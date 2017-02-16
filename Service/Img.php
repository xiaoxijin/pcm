<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/2/12
 * Time: 15:09
 */

namespace Service;

class Img extends \DBService
{

    protected function __format_row_data($row){

        $row['url']=$row['domain'].DS.$row['uri'];
        return $row;
    }
}
