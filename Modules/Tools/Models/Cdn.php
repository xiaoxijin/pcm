<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/7/24
 * Time: 13:29
 */

namespace Module\Tools\Models;
use \Module\Tools\Model as Model;

class Cdn extends Model
{

//    public $table = 'designer_yuyue_compete';
//    public $primary = 'id';
//    public $module_name='Orders';

    public $accessKey;
    public $secretKey;
    public $bucket;
    public $domain;
    public $article_bucket;
    public $article_domain;
    public $vedio_bucket;
    public $vedio_domain;
    public $media_process;
    public $auto_bucket;
    public $auto_domain;


    private $auth;
    public function getAuthInstance(){
        if($this->auth)
            return $this->auth;
        else{
            $this->auth = new \Qiniu\Auth($this->accessKey, $this->secretKey);
            return $this->auth;
        }

    }

    private $UploadManager;
    public function getUploadManagerInstance(){
        if($this->UploadManager)
            return $this->UploadManager;
        else{
            $this->UploadManager = new \Qiniu\Storage\UploadManager();
            return $this->UploadManager;
        }

    }


    private $BucketManager;
    public function getBucketManagerInstance($auth){
        if($this->BucketManager)
            return $this->BucketManager;
        else{
            $this->BucketManager = new \Qiniu\Storage\BucketManager($auth);
            return $this->BucketManager;
        }

    }



    public function __construct(\Xphp $xphp, $db_key = 'master')
    {

        parent::__construct($xphp,$db_key);
        $cdnConfig = $this->config['cdn'][$db_key];
        $this->accessKey = $cdnConfig["accessKey"];
        $this->secretKey = $cdnConfig["secretKey"];
        $this->bucket = $cdnConfig["bucket"];
        $this->domain = $cdnConfig["domain"];
        $this->article_bucket = $cdnConfig["article_bucket"];
        $this->article_domain = $cdnConfig["article_domain"];
        $this->vedio_bucket = $cdnConfig["vedio_bucket"];
        $this->vedio_domain = $cdnConfig["vedio_domain"];
        $this->media_process = $cdnConfig["media_process"];
        $this->auto_bucket = $cdnConfig["autost_bucket"];
        $this->auto_domain = $cdnConfig["autost_domain"];

        $this->Lib("Qiniu");
        $this->getAuthInstance();

    }


    // 上传图片
    // 主要用于认证设计师 认证 过程中 单张图片上传
    public function upLoad($filePath, $fileName){
        // 默认使用 bucket
        $uptoken = $this->getUpToken();
        $uploadMgr = $this->getUploadManagerInstance();
        list($ret, $err) = $uploadMgr->putFile($uptoken['uptoken'], $fileName, $filePath);
        if ($err !== null){
            return $err;
        }else{
            return $ret;
        }
    }


    public function getFileInfo($fileName){


        //初始化BucketManager
        $bucketMgr = $this->getBucketManagerInstance($this->auth);



        //获取文件的状态信息
        list($ret, $err) = $bucketMgr->stat($this->article_bucket, $fileName);
        echo "\n====> $fileName stat : \n";
        if ($err !== null) {
            var_dump($err);
        } else {
            var_dump($ret);
        }


        exit;
    }

    // $prefix 要列取文件的公共前缀
    // $marker 上次列举返回的位置标记，作为本次列举的起点信息。

    public function listFiles($bucket,$prefix='',$marker=''){
        $bucketMgr = $this->getBucketManagerInstance($this->auth);
        $limit = 1000;
        list($iterms, $marker, $err) = $bucketMgr->listFiles($bucket, $prefix, $marker, $limit);
        if ($err !== null) {
            return false;
        } else {

            return $iterms;
        }

    }

    public function listFilesInAutoSt($prefix,$marker){
        return $this->listFiles($this->auto_bucket,$prefix,$marker);
    }
    public function listImgFiles(){
        return $this->listFiles($this->bucket);
    }

    public function listVedioFiles(){
        return $this->listFiles($this->vedio_bucket);
    }

    public function listArticleFiles(){
        return $this->listFiles($this->article_bucket);
    }

    public function deletefile($bucket, $key){
        $bucketMgr = $this->getBucketManagerInstance($this->auth);
        $err = $bucketMgr->delete($bucket, $key);
        if ($err !== null) {
            return false;
        } else {
            return true;
        }
    }

    public function batchdelete($bucket, $keys){
        $bucketMgr = $this->getBucketManagerInstance($this->auth);
        return $bucketMgr->batch($bucketMgr::buildBatchDelete($bucket, $keys));
    }

    public function batchdeleteInAutoSt($keys){
        return $this->batchdelete($this->auto_bucket,$keys);
    }

    public function movefile($res_bucket, $res_key, $tar_bucket, $tar_key){
        $bucketMgr = $this->getBucketManagerInstance($this->auth);
        $err = $bucketMgr->move($res_bucket, $res_key, $tar_bucket, $tar_key);
        if ($err !== null) {
            return false;
        } else {
            return true;
        }
    }

    public function moveFileInVedio($res_key,$tar_key){
        return $this->movefile($this->vedio_bucket,$res_key,$this->vedio_bucket,$tar_key);
    }

    public function moveFileInImg($res_key,$tar_key){
        return $this->movefile($this->bucket,$res_key,$this->bucket,$tar_key);
    }

    public function moveFileInVedioArticle($res_key,$tar_key){
        return $this->movefile($this->article_bucket,$res_key,$this->article_bucket,$tar_key);
    }

    public function deleteImgFile($key){
        return $this->deletefile($this->bucket,$key);
    }



    public function deleteVedioFile($key){
        return $this->deletefile($this->vedio_bucket,$key);
    }



    public function deleteArticleFile($key){
        return $this->deletefile($this->article_bucket,$key);
    }

    public function presaveas(){
        //要进行转码的转码操作
        $fops = "avthumb/mp4/ab/160k/ar/44100/acodec/libfaac/r/30/vb/1250k/vcodec/libx264/s/1280x720/autoscale/1/stripmeta/0";
        $savekey = \Qiniu\base64_urlSafeEncode($this->vedio_bucket.':2016/5/11111test');
        $fops = $fops.'|saveas/'.$savekey;
        $policy = array(
            'persistentOps' => $fops,
            'persistentPipeline' => $this->media_process  //转码时使用的队列名称
        );

        return  array('uptoken' => $this->auth->uploadToken($this->vedio_bucket, null, 3600, $policy));

    }


    public function getUpToken(){
        return  array('uptoken' => $this->auth->uploadToken($this->bucket));
    }


    public function getImgInfo($key){
//        $auth = new Auth($this->accessKey, $this->secretKey);
        $ret = $this->auth->privateDownloadUrl($this->domain.urlencode($key)."?imageInfo");
        return $ret;
    }

    //组装ImageView2参数
    public function getImgView2Param($parame){
        if(!is_array($parame))
            return false;
        $img_param="";
        if(count($parame)>0)
        {

            $img_param='?imageView2/1';
            if (isset($parame['mode']))
                $img_param="?imageView2/{$parame['mode']}";
            //mode :2 为等比缩小不裁剪，1.为等比缩小并居中裁剪

            foreach($parame as $key=>$val)
                $img_param.="/{$key}/{$val}";
            if (!isset($parame['q']))
                $img_param.="/q/30";
        }
        return $img_param;
    }



    //加水印
    public function getImgWatermark2($parame){//
        if(!is_array($parame))
            return false;
        $img_param="";
        if(count($parame)>0)
        {
            $img_param='?watermark/2';
            foreach($parame as $key=>$val)
                $img_param.="/{$key}/{$val}";
        }
        return $img_param;
    }
    /**
     * @return mixed
     */

    private function getDomain($domain)
    {

        if($domain && $this->$domain!=""){
            return $this->$domain;
        }
        else
            return $this->domain;
    }


    public function getDownToken($key='',$param){

        $currentDomain = $param['domain'];
        unset($param['domain']);
        if(isset($param['access']) && $param['access']=='private')
        {
            unset($param['access']);
            $ret = $this->auth->privateDownloadUrl($this->getDomain($currentDomain).urlencode($key).$this->getImgView2Param($param));
        }
        else{
            unset($param['access']);
            $ret = $this->getDomain($currentDomain).$key.$this->getImgView2Param($param);
        }

        return  $ret;
    }



    public function getCdnPhotoInfo($file_name,$param_method,$watermark=null){

        if(!is_null($file_name) &&  $file_name !="" && !is_null($param_method)){
            $download_token= $this->getDownToken($file_name,$this->$param_method());
            $info_token = $this->getImgInfo($file_name);
            return array("downtoken"=>$download_token,'info_token'=>$info_token);
        }else
            return "";
    }


    public function getCdnPhotoUrlByUser($file_name,$param_arr){

        if(!is_null($file_name) &&  $file_name !="" ){
            return $this->getDownToken($file_name,array_merge(array(
                "q"=>"80",
                "interlace"=>"1",
                "format"=>"jpg"
            ),$param_arr));
        }else
            return "";
    }

    public function getCertificate(){
        return array(
            'text'=>\Qiniu\base64_urlSafeEncode('用于家创易认证'),
            'gravity'=>'Center',
            'fontsize'=>'500',
        );
    }
}