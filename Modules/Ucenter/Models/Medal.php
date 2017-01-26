<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/7/24
 * Time: 12:02
 */
namespace Module\Ucenter\Models;
use \Module\Ucenter\Model as Model;
use \Xphp\Tool  as Tools;

class Medal extends Model
{
    public $table = 'medal';
    public $primary='medal_id';
    private $_category=array(
        "institute"=>array(
            "key"=>"associations",
            "title"=>"协会",
            "icon"=>"协会.png",
            "dictionary"=>array(
                "position"=>"position",
                "associations_name"=>"medal_name",
                "period"=>array("start_time","至","end_time"=>array("至今"=>"今"))
            ),
        ),
        "honor"=>array(
            "key"=>"win_prizes",
            "title"=>"获奖",
            "icon"=>"获奖.png",
            "dictionary"=>array(
                "Competition"=>"medal_name",
            ),
        ),

        "job"=>array(
            "key"=>"works",
            "title"=>"任职",
            "icon"=>"任职.png",
            "dictionary"=>array(
                "job"=>"position",
                "company"=>"medal_name",
                "period"=>array("start_time","至","end_time"=>array("至今"=>"今"))
            ),

        ),
    );
    private $_group_by_category=true;

    private $_medals;

    private function initMedals($uid){

        if(isset($this->_medals[$uid]))
            return $this->_medals[$uid];
        $medals = $this->gets(array('uid'=>$uid));
        foreach ($medals as $medals_index=>$medals_val){
            $media_id = $medals_val[$this->primary];
            $category = $medals_val['category'];
            $category_key  = $this->_category[$category]['key'];
            if(!isset($this->_category[$category]))
               continue;

            if(isset($this->_category[$category]['title']))
                $this->_medals[$uid][$category_key]['title']=$this->_category[$category]['title'];

            foreach ($this->_category[$category]['dictionary'] as $dic_key=>$dic_val){
                $this->_medals[$uid][$category_key]['dictionary'][$media_id][$dic_key]=$this->getMediaDicVal($medals_val,$dic_val);
                $this->_medals[$uid][$category_key]['dictionary'][$media_id]['icon']=model("Tools/Cdn")->getCdnPhotoUrlByUser($this->_category[$category]['icon']);
            }



        }

        return $this->_medals[$uid];
    }

    private function getMediaDicVal($medals,$medal_key){
        $dic_val="";
        if(is_array($medal_key)){
            foreach ($medal_key as $medal_key_index=>$medal_key_val){
                if($dic_val=="")
                    $dic_val=$this->_parseMediaDicVal($medals,$medal_key_index,$medal_key_val);
                else
                    $dic_val.=" ".$this->_parseMediaDicVal($medals,$medal_key_index,$medal_key_val);
            }
        }
        else{
            if(isset($medals[$medal_key]))
                $dic_val= $medals[$medal_key];
            else
                $dic_val= $medal_key;
        }

        return $dic_val;
    }


    private function _parseMediaDicVal($medals,$medal_key_index,$medal_key_val){

        if(is_array($medal_key_val) && array_key_exists($medals[$medal_key_index],$medal_key_val)){
            return $medal_key_val[$medals[$medal_key_index]];
        }elseif (is_array($medal_key_val) && !array_key_exists($medals[$medal_key_index],$medal_key_val)
            && isset($medals[$medal_key_index])) {
            return $medals[$medal_key_index];
        }elseif (!is_array($medal_key_val) && isset($medals[$medal_key_val])){
            return $medals[$medal_key_val];
        }else{
            return $medal_key_val;
        }
    }
    public function getMedals($uid,$category){

        $this->initMedals($uid);
        $items = $this->_medals[$uid][$category];
        $items['dictionary'] = array_values($items['dictionary']);
        return $items;
    }
}