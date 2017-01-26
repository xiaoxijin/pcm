<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/12/8
 * Time: 16:31
 */

namespace Module\Tools\Controllers;
use \Module\Tools\Controller as Controller;

class Cdn extends Controller{

    public function delFilesByPrefix($params){
        $cdn = $this->Model("cdn");
        $files = $cdn->listFilesInAutoSt($params['prefix'],"");
        $delFiles =array();
        if(count($files)>0){
            foreach ($files as $file){
                $delFiles[]=$file['key'];
            }
            $res = $cdn->batchdelete($params['bucket'], $delFiles);
        }else{

            $this->setRet(array("code"=>NO_RESULT));
        }
    }

    public function getUploadInfo($params){

        //todo $params['type'] 根据type选择上传策略
        $cdn = $this->Model("cdn");
        $this->setRet(array("data"=>array(
            "accessKey"=>$cdn->accessKey,
            "secretKey"=>$cdn->secretKey,
            "bucket"=>$cdn->auto_bucket,
        )));
    }
}