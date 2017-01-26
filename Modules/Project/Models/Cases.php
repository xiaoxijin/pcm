<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/7/24
 * Time: 13:29
 */

namespace Module\Project\Models;
use \Module\Project\Model as Model;
use Xphp\Tool;

class Cases extends Model
{
    public $table = 'case';
    public $primary='case_id';

    public function getCases($designer_id){
        $items = array();
        $cases =  $this->gets(array('designer_id'=>$designer_id,'closed'=>0));
        foreach ($cases as $case_index=>$case_val){

//            if(!Tool::isValid($case_val['photo']))
//                $case_val['photo']="image/logos/logo_imgbc.png";
            //$items[$case_val[$this->primary]] =array(
            $items[$case_index] =array(

                "number"=>$case_val[$this->primary],
                "edit_url"=>model("Tools/Link")->mkPcLink('ucenter/designer/verify:edit',array($case_val[$this->primary])),
                "preview_url"=>model("Tools/Link")->mkPcLink('case:alldetaildemo',array($case_val[$this->primary])),
                "img_url"=>model("Tools/Cdn")->getCdnPhotoUrlByUser($case_val['photo'],array(
                    "w"=>500,"h"=>500)),
                "sample_describ"=>$case_val['title'],
                "audit"=>$case_val['audit'],
                "sample_info"=>$this->getSampleInfo($case_val),
                "praise"=>$case_val['likes'],
            );
        }
        return $items;
    }

    private function getSampleInfo($case_val){
        $city_name = model("Ucenter/Data_city")->getCityNameById($case_val['city_id']);
        $city_name = $city_name?$city_name."-":"";
        $home_name = model("Ucenter/Home")->getHomeNameById($case_val['home_id']);
        $home_name = $home_name?$home_name."-":"";
        $project_area = $case_val['area']>0? "{$case_val['area']}㎡":"";
        return $city_name.$home_name.$project_area;
    }
}