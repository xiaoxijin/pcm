<?php
/**
 * Created by PhpStorm.
 * User: 肖喜进
 * Date: 2016/7/20
 * Time: 15:35
 */

namespace Module\Ucenter\Models;
use \Module\Ucenter\Model as Model;
use \Xphp\Tool as Tool;

class Member extends Model
{

    public $table = 'member';
    public $primary = 'uid';

    /**
     * 设计师是否用可登陆抢单系统
     * @param $mobile
     * @return array
     * @throws \Exception
     */
    public function chk_designer_info($mobile){
        $user = $this->detail(array("mobile"=>$mobile));

        if ($user['from']!="designer"){
            return array('code'=>DESIGNER_CERT_ERROR);
        }
        elseif (!in_array($user['verify'],array('1','3','5','7'))){
            return array('code'=>VERIFY_MAIL_ERROR);
        }
        elseif ($this->get_case_num($user['uid']) == 0){
            return array('code'=>VERIFY_CASE_ERROR);
        }
        return $user;
    }

    public function get_designer_name($mobile){
        if (!empty($mobile)){
            $this->select = "realname";
            $name = $this->detail(array('mobile'=>$mobile));
            return $name['realname'];
        }
    }

    public function get_member_name($mobile){
        if (!empty($mobile)){
            $this->select = "realname";
            $name = $this->detail(array('mobile'=>$mobile));
            return $name['realname'];
        }
    }

    /**
     * 获取设计师审核通过的案例数量
     * @param $uid
     * @return mixed
     */
    public function get_case_num($uid){
        $sql="SELECT COUNT(1) from `case` WHERE `designer_id`={$uid} AND `audit`=1 AND `closed`=0";

        $count=$this->db->query($sql)->fetch_row()[0];

        return $count;
    }

    public function __format_row_data($row){
        $row['face'] = Tool::isValid($row['face']) ? $row['face'] : $row['from'].'_'.'face.jpg';
        return $row;
    }

}

