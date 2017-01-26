<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/7/24
 * Time: 12:02
 */
namespace Module\Ucenter\Models;
use \Module\Ucenter\Model as Model;
use \Xphp\Tool as Tool;

class Designer_quote extends Model
{
    public $table = 'designer_quote';
    public $primary='designer_quote_id';


    public function getPriceDictionary($params){

        $priceDictionary =array();
        $priceList = $this->gets($params);
        foreach ($priceList as $priceList_key=>$priceList_val){

            $priceDictionary[$priceList_val['city_id']]['city']= $this->Model("Data_city")->getCityNameById($priceList_val['city_id']);
            if($priceList_val['house_type_id']==1)
                $house_type="高层";
            else
                $house_type="别墅";

            $priceDictionary[$priceList_val['city_id']]['dictionary'][$priceList_val['house_type_id']]=array(
                
                'house_type'=>$house_type,
                'edit_allow'=>Tool::getNextEditStatus($priceList_val['updated_time'],30*24*3600,$next_edit_date),
                'anchor'=>model("Tools/Link")->mkPcLink('ucenter/designer/verify:forwardPerson').'#smart_quotation',
                'nextEdit_date'=>"(下次可修改日期{$next_edit_date})",
                'price_list'=>array(
                    array("area"=>"{$priceList_val['first_area_start']}-{$priceList_val['first_area_end']}㎡","price"=>"{$priceList_val['first_area_quote']}/㎡"),
                    array("area"=>"{$priceList_val['second_area_start']}-{$priceList_val['second_area_end']}㎡","price"=>"{$priceList_val['second_area_quote']}/㎡"),
//                    array("area"=>"{$priceList_val['third_area_start']}-{$priceList_val['third_area_end']}㎡","price"=>"{$priceList_val['third_area_quote']}/㎡"),
                    array("area"=>"{$priceList_val['third_area_start']}㎡以上","price"=>"{$priceList_val['third_area_quote']}/㎡"),
                )
            );
        }

        foreach ($priceDictionary as $priceDictionary_num=>$priceDictionary_val){
            $priceDictionary[$priceDictionary_num]['dictionary'] = array_values($priceDictionary[$priceDictionary_num]['dictionary']);
        }
        return array_values($priceDictionary);
    }


}