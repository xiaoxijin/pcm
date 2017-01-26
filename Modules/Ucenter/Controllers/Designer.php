<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/12/9
 * Time: 19:57
 */

namespace Module\Ucenter\Controllers;
use \Module\Ucenter\Controller as Controller;
use \Xphp\Tool as Tool;

class Designer extends Controller
{

    public function getPersonalInfo($params){

       
        if (isset($params['uid']) && Tool::isValid($params['uid']) && (int)$params['uid']>0){

            $uid = $params['uid'];
            $member_info= $this->Model("member")->get($params['uid']);
            $designer_info= $this->Model("designer")->get($params['uid']);

            $likes = $this->Model("member_like")->count(array('uid'=>$uid));
            $likes = is_null($likes)?0:$likes;
            $popularity = $designer_info['views']?(int)$designer_info['views']:0;

            $medal = $this->Model("Medal");
            $this->setRet(array('data'=>array(
                "designer_primary"=>array(
                    "designer_id"=>$uid,
                    "designer_name"=>$member_info['realname'],
                    "header_pic"=>model("Tools/Cdn")->getCdnPhotoUrlByUser($member_info['face'],array(
                        "w"=>200,"h"=>200)),
                    "real_name"=>$this->Model("member_verify")->get($uid)['verify']?true:false,//是否实名认证
                    "popularity"=>$popularity,
                    "likes"=>$likes,
                ),
                "price_sheet"=>$this->Model("designer_quote")->getPriceDictionary(array("designer_id"=>$uid)),
                "labels"=>$this->Model("designer_attr")->getDesignerLabels(array(
                    'uid'=>$uid,
                    'attr_alter_time'=>json_decode($designer_info['attr_alter_time'],true))
                ),
                "win_prizes"=>$medal->getMedals($uid,"win_prizes"),
                "associations"=>$medal->getMedals($uid,"associations"),
                "works"=>$medal->getMedals($uid,"works"),

                "intro"=>array(
                    "title"=>"简介",
                    "edit_allow"=>true,
                    "anchor"=>model("Tools/Link")->mkPcLink('ucenter/designer/verify:forwardPerson'),
                    "nextEdit_date"=>"",
                    "content"=>$designer_info['about'],

                ),
                "sample_count"=>$designer_info['case_num'],
                "samples"=>model("project/cases")->getCases($uid),
//                "comment_count"=>$this->Model("member_comment")->count(array("uid"=>$uid)),
                "comment_count"=>0,
                "comments"=>"暂无评论",
            )));
        }
    }

}