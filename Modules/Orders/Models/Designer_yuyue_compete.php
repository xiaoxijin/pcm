<?php


namespace Module\Orders\Models;
use \Module\Orders\Model as Model;
class Designer_yuyue_compete extends Model
{
    public $table = 'designer_yuyue_compete';
    public $primary = 'id';
    public $dsgStyle;
    public $dsgPersona;

    /**
     * 支付前对订单再次进行验证
     * @param $uid
     * @param $orderNo
     * @param $exporedSecond  订单有效时间 秒
     * @param $result   $result['data']['0'] 订单号   $result['data']['1'] 价格
     * @return bool
     */
    public function getPayOrder($uid,$orderNo,$exporedSecond,& $result){
        $ctime=time()-$exporedSecond;
        $sql="SELECT orderNo,price,id,dsgIntro FROM designer_yuyue_compete WHERE `add_time`>{$ctime} AND `orderNo`='{$orderNo}' AND `designer_id`={$uid} AND `status`='0'";
        $rs =  $this->db->query($sql)->fetch_row();
        if($rs==NULL){
            $result['msg']=NO_RESULT;//没有订单数据
            return false;
        }
        $result['data']=$rs;
        return true;
    }

    /**
     * 获得订单历史记录 
     * 获得所有当前用户的订单
     */
    public function getAllHistory($uid){
        $result = array();
        $designer_id = $uid;
        $result = $this->gets(array("designer_id"=>$designer_id,"status"=>"COND IN:(1,2)"));
        return $result;
    }
    
    
    public function getWeekHistory(){
            
    }
    
    
    public function getMonthHistory(){
            
    }

    /**
     * 获得已抢订单
     * @param $uid 设计师id
     * @param int $p 页码
     * @param int $l 每页条数
     * @param null $result
     * @param int $count 总记录条数
     * @return bool
     */
    public function getCompeteOrders($uid,$p=1,$l=10,& $result=null,& $count=0){
        $ctime=time()-5*60;
        $dsgAttr=model('ucenter/designer_attr');
        $dsgAttr->select = "attr_value_id";
        $this->dsgStyle = array_column($dsgAttr->gets(array("uid"=>$uid,"attr_id"=>16)),'attr_value_id','attr_value_id');//设计师设计风格
        $this->dsgPersona= array_column($dsgAttr->gets(array("uid"=>$uid,"attr_id"=>20)),'attr_value_id','attr_value_id');//设计师个性标签

        $limit = $this->_limit($p, $l);
        //计算总记录数
//        $count_sql="SELECT COUNT(1) FROM designer_yuyue_compete c LEFT JOIN designer_yuyue d ON d.yuyue_id=c.yuyue_id WHERE (((c.`status`='2' AND d.`designer_id`=0) OR (c.`status`='1' AND d.`choose`='0') OR (c.`status`='0' AND c.`add_time`>{$ctime})) OR d.`designer_id`={$uid}) AND c.`designer_id`={$uid}";

        $count_sql="SELECT COUNT(1) FROM designer_yuyue_compete c LEFT JOIN designer_yuyue d ON d.yuyue_id=c.yuyue_id WHERE c.`designer_id`={$uid} AND d.`type`=2";
        $count=$this->db->query($count_sql)->fetch_row()['0'];

        if($count==0){
            $result['msg']=NO_RESULT;//没有订单数据
            return false;
        }
//        $sql="SELECT c.id AS aa,c.yuyue_id AS bb,d.course_key,d.interview_time,d.interview_add,c.orderNo,c.status,c.price,d.content,d.designer_id,d.userLable,(7-(SELECT COUNT(c.id) FROM designer_yuyue_compete c WHERE  ((c.status='0' AND  c.add_time>$ctime) OR c.`status`='1') AND c.id<=aa AND c.yuyue_id=bb)) AS spare_spots FROM designer_yuyue_compete c LEFT JOIN designer_yuyue d ON d.yuyue_id=c.yuyue_id WHERE (((c.`status`='2' AND d.`designer_id`=0) OR (c.`status`='1' AND d.`choose`='0') OR (c.`status`='0' AND c.`add_time`>{$ctime})) OR d.`designer_id`={$uid}) AND c.`designer_id`={$uid} {$limit}";

//        $sql="SELECT c.id AS aa,c.yuyue_id AS bb,d.course_key,d.interview_time,d.interview_add,c.orderNo,c.status,c.is_refund,c.add_time,c.price,d.content,d.designer_id,d.userLable,(7-(SELECT COUNT(c.id) FROM designer_yuyue_compete c WHERE  ((c.status='0' AND  c.add_time>$ctime) OR c.`status`='1') AND c.id<=aa AND c.yuyue_id=bb)) AS spare_spots FROM designer_yuyue_compete c LEFT JOIN designer_yuyue d ON d.yuyue_id=c.yuyue_id WHERE c.`designer_id`={$uid} {$limit}";

        $sql = <<<EOF
    SELECT c.id AS aa,c.yuyue_id AS bb,d.course_key,d.interview_time,d.interview_add,c.orderNo,c.status,c.is_refund,c.add_time,c.price,d.content,d.designer_id,d.userLable,
        (7-(SELECT COUNT(c.id) FROM designer_yuyue_compete c WHERE  ((c.status='0' AND  c.add_time>$ctime) OR c.`status`='1') AND c.id<=aa AND c.yuyue_id=bb)) AS spare_spots
    FROM designer_yuyue_compete c LEFT JOIN designer_yuyue d ON d.yuyue_id=c.yuyue_id WHERE c.`designer_id`={$uid} AND d.`type`=2 {$limit}
EOF;



        $rs =  $this->db->query($sql);

        $dsg_course=$this->Model('designer_course');

        $test = $dsg_course->gets();

        while($row = $rs->fetch()){
            $row = $this->__formatMatch($row,$ctime,$uid);

//            if($row['course_key']=='accept_dsg'){
//                $row['course_key_class'] = 'red quota';
//            }else{
//                $row['course_key_class'] = 'quota';
//            }


            $items[] = $row;
        }
        $result['data']=  $items;
//        var_dump($items);
        return true;
    }

    /**
     * 获得指定预约订单
     * @param $uid 设计师id
     * @param int $p 页码
     * @param int $l 每页条数
     * @param null $result
     * @param int $count 总记录条数
     * @return bool
     */
    public function getAppointOrders($uid,$p=1,$l=10,& $result=null,& $count=0){
        $dsgAttr=model('ucenter/designer_attr');
        $dsgAttr->select = "attr_value_id";
        $this->dsgStyle = array_column($dsgAttr->gets(array("uid"=>$uid,"attr_id"=>16)),'attr_value_id','attr_value_id');//设计师设计风格
        $this->dsgPersona= array_column($dsgAttr->gets(array("uid"=>$uid,"attr_id"=>20)),'attr_value_id','attr_value_id');//设计师个性标签

        $limit = $this->_limit($p, $l);

        //计算总记录数
        $count_sql="SELECT COUNT(1) FROM designer_yuyue_compete c LEFT JOIN designer_yuyue d ON d.yuyue_id=c.yuyue_id WHERE c.`designer_id`={$uid} AND d.`type`=1";
        $count=$this->db->query($count_sql)->fetch_row()['0'];
        if($count==0){
            $result['msg']=NO_RESULT;//没有订单数据
            return false;
        }

        $sql = <<<EOF
        SELECT d.course_key,d.interview_time,d.interview_add,c.orderNo,c.add_time,c.price,d.content,d.interview_time_stamp,d.userLable
        FROM designer_yuyue_compete c LEFT JOIN designer_yuyue d ON d.yuyue_id=c.yuyue_id WHERE c.`designer_id`={$uid} AND d.`type`=1 {$limit}
EOF;
        $rs =  $this->db->query($sql);

        while($row = $rs->fetch()){
            $row = $this->__formatAppoint($row);
            $items[] = $row;
        }
        $result['data']=  $items;
        return true;

    }

    /**
     * 从抢单列表去付款页面
     * @param $uid 设计师id
     * @param $orderNo 抢单订单号
     * @param null $result
     * @param int $count
     */
    public function CompeteOrderToPay($uid,$orderNo,& $result=null){
        $ctime=time()-5*60;
        $dsgAttr=model('ucenter/designer_attr');
        $dsgAttr->select = "attr_value_id";
        $this->dsgStyle = array_column($dsgAttr->gets(array("uid"=>$uid,"attr_id"=>16)),'attr_value_id','attr_value_id');//设计师设计风格
        $this->dsgPersona= array_column($dsgAttr->gets(array("uid"=>$uid,"attr_id"=>20)),'attr_value_id','attr_value_id');//设计师个性标签

        $sql=<<<EOF
SELECT c.id AS aa,c.yuyue_id AS bb,c.orderNo,c.status,c.price,d.interview_time,d.interview_add,d.content,d.designer_id,d.userLable,c.status,c.add_time,c.dsgIntro,
((SELECT COUNT(c.id) FROM designer_yuyue_compete c WHERE  ((c.status='0' AND  c.add_time>$ctime) OR c.`status`='1') AND c.id<=aa AND c.yuyue_id=bb)) AS rank,
((SELECT COUNT(c.id) FROM designer_yuyue_compete c WHERE  ((c.status='0' AND  c.add_time>$ctime) OR c.`status`='1') AND c.yuyue_id=bb)) AS num
FROM designer_yuyue_compete c LEFT JOIN designer_yuyue d ON d.yuyue_id=c.yuyue_id WHERE c.orderNo='{$orderNo}' AND c.`designer_id`={$uid}
EOF;

        $rs =  $this->db->query($sql);
        while($row = $rs->fetch()){
            $row = $this->__formatMatch($row);
            $items[] = $row;
        }

        if($items['0']['status']=='3'){//已经付款
            $result['msg']='have_pay';
            return false;
//        }elseif($items['0']['status']=='0' && $ctime>=$items['0']['add_time']){//付款超时
        }elseif($items['0']['status']=='2'){//付款超时
            $result['msg']='pay_overtime';
            return false;
        }else{
            $result['data']=  $items;
            return true;
        }
    }

    //从预约订单(业主预约指定设计师订单) 去付款页面
    public function AppointOrderToPay($uid,$orderNo,& $result=null){
        $dsgAttr=model('ucenter/designer_attr');
        $dsgAttr->select = "attr_value_id";
        $this->dsgStyle = array_column($dsgAttr->gets(array("uid"=>$uid,"attr_id"=>16)),'attr_value_id','attr_value_id');//设计师设计风格
        $this->dsgPersona= array_column($dsgAttr->gets(array("uid"=>$uid,"attr_id"=>20)),'attr_value_id','attr_value_id');//设计师个性标签

        //计算总记录数
        $count_sql="SELECT COUNT(1) FROM designer_yuyue_compete c LEFT JOIN designer_yuyue d ON d.yuyue_id=c.yuyue_id WHERE c.orderNo='{$orderNo}' AND c.`designer_id`={$uid} AND d.`type`=1";
        $count=$this->db->query($count_sql)->fetch_row()['0'];
        if($count==0){
            $result['msg']=NO_RESULT;//没有订单数据
            return false;
        }

        $sql = <<<EOF
        SELECT d.course_key,d.interview_time,d.interview_add,c.orderNo,c.add_time,c.price,d.content,d.interview_time_stamp,d.userLable
        FROM designer_yuyue_compete c LEFT JOIN designer_yuyue d ON d.yuyue_id=c.yuyue_id WHERE c.`designer_id`={$uid} AND d.`type`=1 AND c.orderNo='{$orderNo}'
EOF;
        $rs =  $this->db->query($sql);

        while($row = $rs->fetch()){
            $row = $this->__formatAppoint($row);
            $items[] = $row;
        }
        if($items['0']['status']=='3' || $items['0']['status']=='4'){//已经付款
            $result['msg']='have_pay';
            return false;
        }elseif($items['0']['status']=='2'){//付款超时
            $result['msg']='pay_overtime';
            return false;
        }else{
            $result['data']=  $items;
            return true;
        }

    }

    public function grabDesignerCount($yuyue_id){

        $sql ="select COUNT(dc.id) as grabCount from designer_yuyue_compete dc WHERE dc.yuyue_id=".$yuyue_id." and dc.trade_status='TRADE_SUCCESS'";

        $grabCount =  $this->db->query($sql)->fetch_row()['0'];


        return array('grabCount'=>$grabCount);


    }

    private function __formatMatch($row,$ctime,$dsgId){
        unset($row['aa']);
        unset($row['bb']);
        $content=json_decode($row['content'],true);
        unset($row['content']);
        $row['yuyue_sn']=$row['orderNo'];
        unset($row['orderNo']);
        $row['addr'] =$content['city'].$content['city_area'];
        $row['community']=$content['home'];
        $row['area']=$content['area'];
        $row['get_price']=$row['price'];
        unset($row['price']);

        $row['fail_time'] = $row['add_time'] + 5*60;
        if($row['status']=='0' && $ctime>=$row['add_time']){
            $row['status'] = 2;
            $row['status_name'] = '付款超时';
        }elseif($row['status']=='0'){
            $row['status'] = 1;
            $row['status_name'] = '未付款';
        }elseif($row['status']=='1'){
            $row['status'] = 3;
            $row['status_name'] = '等待客户选择';
        }elseif($row['status']=='2' && $row['designer_id']==0){
            $row['status'] = 4;
            $row['status_name'] = '即将约见';
        }elseif($row['status']=='2' && $row['designer_id']==$dsgId){
            $row['status'] = 6;
            $row['status_name'] = '已确定';
        }elseif($row['status']=='2'){
            $row['status'] = 7;
            $row['status_name'] = '成交失败';
        }elseif(($row['status']=='3' || $row['status']=='4') && $row['is_refund']!='1'){
            $row['status'] = 5;
            $row['status_name'] = '未约见';
        }elseif(($row['status']=='3' || $row['status']=='4') && $row['is_refund']=='1'){
            $row['status'] = 8;
            $row['status_name'] = '已退款 ';
        }

        unset($row['designer_id']);

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
        if($PersonaAttr){
            $userPersona = array_column($PersonaAttr->fetchall(),'title','attr_value_id');
        }else{
            return $row;
        }

        $userStyle=join($userStyle,',');
        $StyleSql = "SELECT * FROM data_attr_value WHERE attr_value_id IN ($userStyle)";
        $StyleAttr =  $this->db->query($StyleSql);
        if($StyleAttr){
            $userStyle = array_column($StyleAttr->fetchall(),'title','attr_value_id');
        }else{
            return $row;
        }

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

    private function __formatAppoint($row){
        $content=json_decode($row['content'],true);
        unset($row['content']);
        $row['yuyue_sn']=$row['orderNo'];
        unset($row['orderNo']);
        $row['addr'] =$content['city'].$content['city_area'];
        $row['community']=$content['home'];
        $row['area']=$content['area'];
        $row['get_price']=$row['price'];
        unset($row['price']);

        if($row['interview_time_stamp']-$row['add_time']>24*3600)
        {
            $exporedSecond =  24*3600;
        }else{
            $exporedSecond = $row['interview_time_stamp']-$row['add_time']-(4*3600);
        }
        $row['fail_time'] = $row['add_time'] + $exporedSecond;
        //todo  订单状态
        if($row['course_key'] == 'access_sys'){
            $row['status'] = 1;
            $row['status_name'] = '未付款';
        }elseif($row['course_key'] == 'nopay'){
            $row['status'] = 2;
            $row['status_name'] = '付款超时';
        }elseif($row['course_key'] == 'interview'){
            $row['status'] = 3;
            $row['status_name'] = '即将约见';
        }else{
            $row['status'] = 4;
            $row['status_name'] = '已约见';
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



}
