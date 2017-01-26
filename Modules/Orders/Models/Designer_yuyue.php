<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/7/24
 * Time: 12:02
 */
namespace Module\Orders\Models;
use \Module\Orders\Model as Model;
use Xphp\Tool;
class Designer_yuyue extends Model
{
    public $table = 'designer_yuyue';
    public $primary='yuyue_id';
    public $course_key_where;
    public $dsgStyle;
    public $dsgPersona;
    public $desig_course=array();

    private function initDesginerCourse(){

        if(!count($this->desig_course)>0){
            $desig_course = $this->Model('designer_course')->gets();


        }

        foreach ($desig_course as $desig_course_num =>$desig_course_val){
            $this->desig_course[$desig_course_val['course_key']]=$desig_course_val;

            switch ($desig_course_val['course_key'])
            {
                case "interview":
                    $this->desig_course[$desig_course_val['course_key']]['status_name']="约谈中";
                    $this->desig_course[$desig_course_val['course_key']]['status']="1";
                    $this->desig_course['1']['course_key'][]=$desig_course_val['course_key'];
                        break;
                case "contract" || "plan_confirm_dsg":
                    $this->desig_course[$desig_course_val['course_key']]['status_name']="平面图";
                    $this->desig_course[$desig_course_val['course_key']]['status']="2";
                    $this->desig_course['2']['course_key'][]=$desig_course_val['course_key'];

                        break;

                case "rendering_confirm_yz" || "work_confirm_dsg":
                    $this->desig_course[$desig_course_val['course_key']]['status_name']="施工图";
                    $this->desig_course[$desig_course_val['course_key']]['status']="3";
                    $this->desig_course['3']['course_key'][]=$desig_course_val['course_key'];
                    break;

                case "work_confirm_yz" || "decoration_follow" || "completion":
                    $this->desig_course[$desig_course_val['course_key']]['status_name']="施工图";
                    $this->desig_course[$desig_course_val['course_key']]['status']="4";
                    $this->desig_course['4']['course_key'][]=$desig_course_val['course_key'];
                    break;

                default:
                    $this->desig_course[$desig_course_val['course_key']]['status_name']="已取消";
                    $this->desig_course[$desig_course_val['course_key']]['status']="5";
                    $this->desig_course['5']['course_key'][]=$desig_course_val['course_key'];

            }
        }
    }

    private function getCourseWhere($courseStatus){


        //$where = str_replace(' or ','',$where);
        foreach ($this->desig_course[$courseStatus]['course_key'] as  $course_key_num=>$course_key_val)
        {
            $this->course_key_where = $this->course_key_where . " and dy.course_key='" . $course_key_val."'";
        }
        return $this->course_key_where;

    }

    public function getOrderCount($designer_id,$course_status){

        $this->initDesginerCourse();
        $sql="select COUNT(1) from designer_yuyue dy,design_course dc where dc.course_key=dy.course_key and dy.designer_id=".$designer_id.$this->getCourseWhere($course_status);

        $count=$this->db->query($sql)->fetch_row()[0];

        return $count;
    }

    public function getOrders($designer_id){

        $this->initDesginerCourse();

        $sql="select dy.yuyue_id,dy.yuyue_sn as number,FROM_UNIXTIME(dy.add_time,\"%Y-%c-%e %k:%i\") as time,dy.course_key ,dy.content from designer_yuyue dy,design_course dc where dc.course_key=dy.course_key and dy.designer_id=".$designer_id;

        $rs =  $this->db->query($sql);
        while($row = $rs->fetch()){

            $items[] = $this->__orderFormat($row);
        }

        return $items;
    }

    private function __orderFormat($row){
        $content = json_decode($row['content'],true);
        $row['project_info'] =$content['city'].",".$content['home'].",".$content['area']."㎡,".$content['project_type'];
        unset($row['content']);
        $row['owner_info']=join($this->get_order_style($row['yuyue_id']),",");

        $row['status_name']=$this->desig_course[$row['course_key']]['status_name'];
        $row['status']=$this->desig_course[$row['course_key']]['status'];
        unset($row['course_key']);
        unset($row['yuyue_id']);
        return $row;

    }


    public function MatchAllOrder($uid,$yuyue_sn,$p=1,$l=10,& $result=null,& $count=0)
    {
        $_time = time();
        $time = $_time-24*3600;
        $ctime = $_time-5*60;
        $interview_time_stamp = $_time + 4*3600;//距离下午茶时间大于4小时
        $dsgAttr=model('Ucenter/designer_attr');
        $dsgAttr->select = "attr_value_id";
        $this->dsgStyle = array_column($dsgAttr->gets(array("uid"=>$uid,"attr_id"=>16)),'attr_value_id','attr_value_id');//设计师设计风格
        $this->dsgPersona= array_column($dsgAttr->gets(array("uid"=>$uid,"attr_id"=>20)),'attr_value_id','attr_value_id');//设计师个性标签

        if($yuyue_sn){
            //在支付页面刷新
            $compete_count_sql = <<<EOF
SELECT c.id AS zhifu_id,c.yuyue_id AS designer_yuyue_id,c.orderNo AS yuyue_sn,d.interview_time,d.interview_add,d.content,d.userLable,c.status,c.add_time,c.dsgIntro AS dsgIntro,
((SELECT COUNT(c.id) FROM designer_yuyue_compete c WHERE  ((c.status='0' AND  c.add_time>$ctime) OR c.`status`='1') AND c.id<=zhifu_id AND c.yuyue_id=designer_yuyue_id)) AS rank,
((SELECT COUNT(c.id) FROM designer_yuyue_compete c WHERE (c.status='1' OR  (c.status='0' AND  c.add_time>$ctime)) AND c.yuyue_id=d.yuyue_id)) AS num
FROM designer_yuyue_compete c LEFT JOIN designer_yuyue d ON c.yuyue_id=d.yuyue_id WHERE c.yuyue_id=d.yuyue_id AND d.yuyue_sn='{$yuyue_sn}' AND c.designer_id={$uid} ORDER BY c.id DESC  LIMIT 1
EOF;


            $compete_rs =$this->db->query($compete_count_sql);
            while($compete_row = $compete_rs->fetch()){
                $row = $this->__formatMatch($compete_row);
                $compete_items[] = $row;
            }

            if($compete_items!=NULL ){
                if($compete_items['0']['status']!='0'){
                    $result['msg']='have_pay';
                    return false;
                }elseif($compete_items['0']['status']=='0' && $ctime>=$compete_items['0']['add_time']){
                    $result['msg']='pay_overtime';
                    $compete_items['0']['zhifu_id'];
                    $clearup_payorder_sql="DELETE from designer_yuyue_compete where id=".$compete_items['0']['zhifu_id'];
                    $this->db->query($clearup_payorder_sql);
                    return false;
                }elseif($compete_items['0']['status']=='0' && $compete_items['0']['add_time']>$ctime){
                    $count=1;
                    $result['msg']='0809';//刷新付款页面
                    $result['data']=  $compete_items;
                    return true;
                }
            }
        }



        //获取设计师接单范围
        $designer = model('Ucenter/designer');
        $designer->select="accept_city_id";
        $accept_city_id = $designer->detail(array("uid"=>$uid))['accept_city_id'];

        if(!$accept_city_id){
            $result['msg']=NO_ACCEPT_CITY_ID;//当前设计师接单范围不明
            return false;
        }
        if($yuyue_sn){
            $WYS="d.yuyue_sn='{$yuyue_sn}' AND ";
        }else{
            $WYS="";
        }
        $limit = $this->_limit($p, $l);
        //计算总记录数
//       $count_sql="SELECT COUNT(1) FROM `designer_yuyue` d WHERE {$WYS} d.type='2' AND d.`course_key`!='check' AND d.choose='0' AND d.`dateline`>{$time} AND {$interview_time_stamp} < d.`interview_time_stamp` AND d.city_id IN ({$accept_city_id}) AND (SELECT COUNT(1) FROM designer_yuyue_compete c WHERE c.yuyue_id=d.yuyue_id AND c.designer_id={$uid} AND (c.status='1' OR  (c.status='0' AND  c.add_time>$ctime)))=0 AND (6-(SELECT COUNT(1) FROM designer_yuyue_compete c WHERE (c.status='1' OR  (c.status='0' AND  c.add_time>$ctime)) AND c.yuyue_id=d.yuyue_id))>0";

        $count_sql = <<<EOF
    SELECT COUNT(1) FROM `designer_yuyue` d
    WHERE {$WYS} d.type='2' AND d.`course_key`='access_sys' AND d.city_id IN ({$accept_city_id})
        AND (SELECT COUNT(1) FROM designer_yuyue_compete c WHERE c.yuyue_id=d.yuyue_id AND c.designer_id={$uid} AND (c.status='1' OR  (c.status='0' AND  c.add_time>$ctime)))=0
        AND (6-(SELECT COUNT(1) FROM designer_yuyue_compete c WHERE (c.status='1' OR  (c.status='0' AND  c.add_time>$ctime)) AND c.yuyue_id=d.yuyue_id))>0
EOF;


        $count=$this->db->query($count_sql)->fetch_row()[0];

       if($count==0){
            $result['msg']=NO_RESULT;//没有订单数据
            return false;
        }
//        $sql="SELECT d.interview_time,d.interview_add,d.yuyue_id,d.yuyue_sn,d.content,d.userLable,(6-(SELECT COUNT(c.id) FROM designer_yuyue_compete c WHERE (c.status='1' OR  (c.status='0' AND  c.add_time>$ctime)) AND c.yuyue_id=d.yuyue_id)) AS spare_spots FROM `designer_yuyue` d WHERE {$WYS} d.type='2' AND d.choose='0' AND (SELECT COUNT(1) FROM designer_yuyue_compete c WHERE c.yuyue_id=d.yuyue_id AND c.designer_id={$uid} AND (c.status='1' OR  (c.status='0' AND  c.add_time>$ctime)))=0 AND d.`dateline`>{$time} AND d.`course_key`!='check' AND {$interview_time_stamp} < d.`interview_time_stamp` AND d.city_id IN ({$accept_city_id}) HAVING spare_spots>0 ORDER BY d.dateline ASC {$limit}";

        $sql = <<<EOF
    SELECT d.interview_time,d.interview_add,d.yuyue_id,d.yuyue_sn,d.content,d.userLable,
        (6-(SELECT COUNT(c.id) FROM designer_yuyue_compete c WHERE (c.status='1' OR  (c.status='0' AND  c.add_time>$ctime)) AND c.yuyue_id=d.yuyue_id)) AS spare_spots
    FROM `designer_yuyue` d
    WHERE {$WYS} d.type='2' AND d.`course_key`='access_sys' AND d.city_id IN ({$accept_city_id})
        AND (SELECT COUNT(1) FROM designer_yuyue_compete c WHERE c.yuyue_id=d.yuyue_id AND c.designer_id={$uid} AND (c.status='1' OR  (c.status='0' AND  c.add_time>$ctime)))=0
        HAVING spare_spots>0 ORDER BY d.dateline ASC {$limit};
EOF;


        $rs =  $this->db->query($sql);
        while($row = $rs->fetch()){

            $row = $this->__formatMatch($row);
            $items[] = $row;
        }
        $result['data']=  $items;
        return true;

    }

    private function __formatMatch($row){

        $row['fail_time'] = $row['add_time'] + 5*60;

        $content=json_decode($row['content'],true);
        unset($row['content']);
        $row['addr'] =$content['city'].$content['city_area'];
        $row['community']=$content['home'];
        $row['area']=$content['area'];

        //计算出订单价格
        if($content['area']<=150){
            $row['get_price']=100;
        }elseif($content['area']>=151 && $content['area']<=300){
            $row['get_price']=150;
        }elseif($content['area']>=301 && $content['area']<=500){
            $row['get_price']=200;
        }elseif($content['area']>=501){
            $row['get_price']=300;
        }

        //标签匹配度
        $userLableAttr = json_decode($row['userLable'], true);
        unset($row['userLable']);
        foreach($userLableAttr as $k=>$v){
            if($k == 16){
                foreach($v as $kk=>$vv){
                    $userStyle[]=$vv;

                }
            }
            if($k == 20){
                foreach($v as $kk=>$vv){
                    $userPersona[]=$vv;

                }
            }
        }
        $userPersona=join($userPersona,',');
        $PersonaSql = "SELECT * FROM data_attr_value WHERE attr_value_id IN ($userPersona)";
        $PersonaAttr =  $this->db->query($PersonaSql);
        $userPersona = array_column($PersonaAttr->fetchall(),'title','attr_value_id');

        $userStyle=join($userStyle,',');
        $StyleSql = "SELECT * FROM data_attr_value WHERE attr_value_id IN ($userStyle)";
        $StyleAttr =  $this->db->query($StyleSql);
        $userStyle = array_column($StyleAttr->fetchall(),'title','attr_value_id');

        $row['person_label']['match']=$row['person_label']['unmatch']=$row['style_label']['match']=$row['style_label']['unmatch']=array();
        foreach($userPersona as $k=>$v){
            if(in_array($k,$this->dsgPersona)){
                $row['person_label']['match'][]=$v;
            }else{
                $row['person_label']['unmatch'][]=$v;
            }
        }
        foreach($userStyle as $k=>$v){
            if(in_array($k,$this->dsgStyle)){
                $row['style_label']['match'][]=$v;
            }else{
                $row['style_label']['unmatch'][]=$v;
            }
        }
        return $row;
    }



    private function _limit($page, $limit = 0)
    {
        $limit = intval(0 < $limit ? $limit : 0);
        $start = (max(intval($page), 1) - 1) * $limit;
        if ((0 < $start) && (0 < $limit)) {
            return " LIMIT $start, $limit";
        } else if ($limit) {
            return " LIMIT $limit";
        } else if ($start) {
            return " LIMIT $start";
        } else {
            return "";
        }
    }



    /**
     * @param $uid 设计师uid
     * @param $yuyue_id 业主预约的订单id
     */
    public function matchLabel($uid, $yuyue_id)
    {
        // 获取设计师个人的 设计风格 兴趣爱好
        $dsgAttr = model('Ucenter/designer_attr');
        $dsgAttr->select = "attr_value_id";
        $dsgStyle = array_column($dsgAttr->gets(array("uid" => $uid, "attr_id" => 19)), 'attr_value_id', 'attr_value_id');//设计师设计风格
        $dsgPersona = array_column($dsgAttr->gets(array("uid" => $uid, "attr_id" => 20)), 'attr_value_id', 'attr_value_id');//设计师个性标签
        // 获取订单中的（业主选择） 设计风格 兴趣爱好

        $this->select = 'userLable';
        $orderLabel = $this->get($yuyue_id);
        $orderStyle = explode(',', $orderLabel['19']);
        $orderPersona = explode(',', $orderLabel['20']);
        $matchStyle = array_intersect($dsgStyle,$orderStyle);
        // 订单中有设计师没有的style
        $unmatchStype = array_diff(array_merge($orderStyle, $dsgStyle),$orderStyle);
        $matchPersona = array_intersect($orderPersona, $dsgPersona);
        // 订单中有设计师没有的兴趣爱好
        $unmatchPersona = array_diff(array_merge($orderPersona, $dsgPersona), $dsgPersona);
        $result = array();

        $dsgAttr->select = 'title';
        if (!empty($matchStyle)){
            $tmp = array();
            foreach ($matchStyle as $value){
                $tmp = array_push($tmp, $dsgAttr->get($value)['title']);
            }
            $result['matchStyleName'] = $tmp;
        }
        if (!empty($matchPersona)){
            $tmp = array();
            foreach ($matchStyle as $value){
                $tmp = array_push($tmp, $dsgAttr->get($value)['title']);
            }
            $result['matchPersona'] = $tmp;
        }
        if (!empty($unmatchPersona)){
            $tmp = array();
            foreach ($matchStyle as $value){
                $tmp = array_push($tmp, $dsgAttr->get($value)['title']);
            }
            $result['unmatchPersona'] = $tmp;
        }
        if (!empty($unmatchStype)){
            $tmp = array();
            foreach ($matchStyle as $value){
                $tmp = array_push($tmp, $dsgAttr->get($value)['title']);
            }
            $result['unmatchStype'] = $tmp;
        }
        return $result;
    }

    /**
     *  根据给定的uid 得到 realname
     */
    public function get_user_realname($uid){
        $this->select = 'realname';
        $realname = model('ucenter/member')->get($uid);
        return $realname['realname'];
    }


    /**
     *  根据给定的uid 得到 member ifno
     */
    public function get_user_info($uid){

        $userinfo = model('ucenter/member')->get($uid);
        return $userinfo;
    }


    /**
     *  得到订单相关信息
     *  订单地址，面积，抢单费用，剩余名额
     */
    public function get_order_info($yuyue_id){
        $this->select = '*';
        $order = $this->get($yuyue_id);
        return $order;
    }

    /**
     *  获取订单中的设计风格标签的名字
     */
    public function get_order_style($yuyue_id){
        // 获取订单中的（业主选择） 设计风格
        $this->select = 'userLable';
        $order = $this->get($yuyue_id)['userLable'];
        $orderStyle = (array)json_decode($order);
        $attr = model('ucenter/data_attr_value');
        $attr->select = 'title';
        $styleName = array();
        foreach ($orderStyle as $k=>$attr_value_id_array){
            if ($k == 16){
                foreach ($attr_value_id_array as $attr_value_id){
                    $tmp = $attr->get($attr_value_id)['title'];
                    if(trim($tmp)!="")
                        array_push($styleName, $tmp);
                }
            }
        }
        return $styleName;
    }

    /**
     *  获取订单中的个性标签的名字
     */
    public function get_order_person($yuyue_id){
        // 获取订单中的（业主选择） 设计风格
        $this->select = 'userLable';
        $order = $this->get($yuyue_id)['userLable'];
        $orderStyle = (array)json_decode($order);
        $attr = model('ucenter/data_attr_value');
        $attr->select = 'title';
        $styleName = array();
        foreach ($orderStyle as $k=>$attr_value_id_array){
            if ($k == 20){
                foreach ($attr_value_id_array as $attr_value_id){
                    $tmp = $attr->get($attr_value_id)['title'];
                    if(trim($tmp)!="")
                        array_push($styleName, $tmp);
                }
            }
        }
        return $styleName;
    }


}
