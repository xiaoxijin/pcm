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

class Designer_attr extends Model
{
    public $table = 'designer_attr';
    public $primary='uid';

    private $_designer_labels_content;
    private $_labe_icon = array(
        16=>"风格.png",
        20=>"个性.png",
    );

    public function getDesignerLabels($params){


        $items= array(
            'label_title'=>"标签",
            'edit_allow'=>Tool::getNextEditStatus($params['attr_alter_time'],30*24*3600,$next_edit_date),
            'nextEdit_date'=>"(下次可修改日期{$next_edit_date})",
            'anchor'=>model("Tools/Link")->mkPcLink('ucenter/designer/verify:forwardPerson').'#smart_match_labels',
        );
        $sql="select dea.attr_value_id,dea.attr_id,da.title,dav.title as name from designer_attr dea , data_attr_value dav , data_attr da where  (dea.attr_id=16 Or dea.attr_id=20) And dav.attr_value_id = dea.attr_value_id and dea.attr_id=da.attr_id and dea.uid={$params['uid']}";

        $rs =  $this->db->query($sql);

        while($row = $rs->fetch()){
            $items['dictionary'][$row['attr_id']] = array(
                'title'=>$row['title'],
                'icon'=>model("Tools/Cdn")->getCdnPhotoUrlByUser($this->_labe_icon[$row['attr_id']]),
                "content"=>$this->designerLabelsFormat($row),
            );
        }
        $this->_designer_labels_content = "";
        $items['dictionary']=array_values($items['dictionary']);
        return $items;
    }

    private function designerLabelsFormat($row){
        if (!isset($this->_designer_labels_content[$row['attr_id']]))
        {
            $this->_designer_labels_content[$row['attr_id']] = $row['name'];
        }
        else
        {
            $this->_designer_labels_content[$row['attr_id']] = $this->_designer_labels_content[$row['attr_id']] . "," . $row['name'];
        }

        return $this->_designer_labels_content[$row['attr_id']];
    }


    /**
     *  比较订单中和设计师的style
     *  $orderStyle 为一维数组
     *  找到匹配项
     */
    public function matchStyle(array $orderStyle, $uid)
    {
        if(!empty($orderStyle)){
            /**
             * 拉取当前订单的 style
             */
            $arrStyle = array();
            $this->select = "attr_value_id";
            $tmp = $this->gets(array("uid"=>$uid,"attr_id"=>16));
            foreach($tmp as $k=>$v){
                array_push($arrStyle, $v['attr_value_id']);
            }
            $res = array_intersect($arrStyle, $orderStyle);
            return $res;
        }    
    }

    /**
     *  比较订单中和设计师的style
     *  $orderStyle 为一维数组
     *  找到不匹配的项
     */
    public function unmatchStyle(array $orderStyle, $uid)
    {
        if(!empty($orderStyle)){
            /**
             * 拉取当前订单的 style
             */
            $arrStyle = array();
            $this->select = "attr_value_id";
            $tmp = $this->gets(array("uid"=>$uid,"attr_id"=>16));
            foreach($tmp as $k=>$v){
                array_push($arrStyle, $v['attr_value_id']);
            }
            $res = array_diff($arrStyle, $orderStyle);
            return $res;
        }    
    }


}
