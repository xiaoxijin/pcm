<?php
/**
 * Copy Right jcy.cc
 * Each engineer has a duty to keep the code elegant
 * author xiaoxijin
 */
namespace Client;

class Curl
{
    static public function request($params=array()){
        $curl = curl_init($params['url']);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $params['method']);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $params['data']);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER,$params['header']);
        return  curl_exec($curl);
    }
}