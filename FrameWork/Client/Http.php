<?php
/**
 * Copy Right jcy.cc
 * Each engineer has a duty to keep the code elegant
 * Author shzhrui<anhuike@gmail.com>
 * $Id: http.mdl.php 2034 2013-12-07 03:08:33Z langzhong $
 */

namespace Xphp\Client;
class Http
{
    static private $configs;
    static public function post($params){

        self::$configs = self::$configs?? getCfg("client")['master'];
        $host =  $params['_host']?? self::$configs['host'];
        $port =  $params['_port']?? self::$configs['port'];
        $path =  $params['_path']?? self::$configs['path'];
        $type =  $params['_type']?? self::$configs['type'];
        $url =$host.':'.$port.$path.$type;

        $data = array(
            "guid" => self::getUnicodeGuid(),
            "api" => $params['api'],
        );
        $data_string = "params=" . urlencode(json_encode($data));

        return Curl::request(array(
            'url'=>$url,
            'method'=>"post",
            'data'=>$data_string,
            'header'=>array(
                'Connection: Keep-Alive',
                'Keep-Alive: 300',
            )
        ));
    }

    static private function getUnicodeGuid(){
        return md5(mt_rand(1000000, 9999999) . mt_rand(1000000, 9999999) . microtime(true));
    }

    public function noticeHomeOwnerOrderClosed($api_params){
        $params['type']='multinoresult';

        $params['api']=array(
            "sms_noticeHomeOwner" => array(
                "name" => "tools/HomeOwnerMail/noticeHomeOwnerOrderClosed",
                "params" => array(
                    "to"=>$api_params['to'],
                    "realname"=>$api_params['realname'],
                    "yuyue_sn"=>$api_params['yuyue_sn'],
                )
            ),
        );
        $this->rpc_client($params);
    }

    public function NoticeDesignerFinishedInfo($api_params){
        $params['type']='multinoresult';

        $params['api']=array(
            "sms_noticeDesigner" => array(
                "name" => "tools/designerMail/finishedInfoVerify",
                "params" => array(
                    "to_mails"=>$api_params['to_mails'],
                )
            ),
        );
        $this->rpc_client($params);

    }

    public function sendMarket($api_params){

        $params['type']='multinoresult';

        $params['api']=array(
            "sms_noticeDesigner" => array(
                "name" => "tools/mail/toMarketDesignerGrabOrderSuccess",
                "params" => array(
                    "yuyue_sn"=>$api_params['yuyue_sn'],
                    "designer_real_name"=>$api_params['designer_real_name'],
                    "to"=>"market@jcy.cc",
                )

            ),
        );
        $this->rpc_client($params);
    }

    public function designerGrabOrderSuccess($api_params){
        $params['type']='multinoresult';


        $params['api']=array(

            "sms_noticeDesigner" => array(
                "name" => "tools/DesignerMail/designerGrabOrderSuccess",
                "params" => array(
                    "sort"=>$api_params['to_designer_mail']['sort'],
                    "realname"=>$api_params['to_designer_mail']['realname'],
                    "to" => $api_params['to_designer_mail']['to'],
                    "price" =>$api_params['to_designer_mail']['price'],
                    "yuyue_sn" =>$api_params['to_designer_mail']['yuyue_sn'],
                    "date" =>$api_params['to_designer_mail']['date'],
                    "area_name" =>$api_params['to_designer_mail']['area_name'],
                    "home_name" =>$api_params['to_designer_mail']['home_name'],
                    "project_type" =>$api_params['to_designer_mail']['project_type'],
                    "area_size" =>$api_params['to_designer_mail']['area_size'],
                )
            ),
        );

        $this->rpc_client($params);
    }

    public function designerGrabOrderfinished($api_params){
        $params['type']='multinoresult';

        $params['api']=array(

            "sms_noticeDesigner" => array(
                "name" => "tools/HomeOwnerMail/designerGrabOrderFinishedToHomeOwner",
                "params" => array(
                    "realname"=> $api_params['to_homeOwner_mail']['realname'],
                    "to" => $api_params['to_homeOwner_mail']['to'],
                    "grabCount" => $api_params['to_homeOwner_mail']['grabCount'],
                    "price" =>$api_params['to_homeOwner_mail']['price'],
                    "yuyue_sn" =>$api_params['to_homeOwner_mail']['yuyue_sn'],
                    "date" =>$api_params['to_homeOwner_mail']['date'],
                    "address" =>$api_params['to_homeOwner_mail']['address'],
                    "area_name" =>$api_params['to_homeOwner_mail']['area_name'],
                    "home_name" =>$api_params['to_homeOwner_mail']['home_name'],
                    "project_type" =>$api_params['to_homeOwner_mail']['project_type'],
                    "area_size" =>$api_params['to_homeOwner_mail']['area_size'],
                )
            ),
        );

        $this->rpc_client($params);
    }



}