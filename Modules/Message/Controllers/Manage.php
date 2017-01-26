<?php
namespace Module\Message\Controllers;
use \Module\Message\Controller as Controller;

class Manage extends Controller
{

    public function getMessages($params){
        if (isset($params['uid']) && $params['uid']>0) {
            $uid = $params['uid'];

            $messagesInfo = $this->Model("manage")->gets(array("receive_id" => $params['uid'], "closed" => 0));
            $this->setRet(array('data' => array("des_message" => $messagesInfo)));
        }
    }


    public function setRead($params){

        $message_id = $params['message_id'];
        $ret = $this->Model("manage")->set($message_id,array("state"=>"1"));
        $this->setRet(array('data'=>array("ret"=>$ret)));
    }

    public function delete($params){

        $message_id = $params['message_id'];
        $ret=true;
        if(is_array($message_id))
            foreach ($message_id as $message_id_val){
                $ret &= $this->Model("manage")->set($message_id_val,array("closed"=>"1"));
            }
        else
            $ret = $this->Model("manage")->set($message_id,array("closed"=>"1"));

        $this->setRet(array('data'=>array("ret"=>$ret)));
    }
}
