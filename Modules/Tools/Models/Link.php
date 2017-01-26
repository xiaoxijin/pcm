<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/12/13
 * Time: 11:03
 */

namespace Module\Tools\Models;
use \Module\Tools\Model as Model;

class Link extends Model
{
    /**
     * 生成连接函数
     * $ctl  参数由 "ctl:act" 组合
     * $args 可以是数组也可以是字符串，这里会传递到action方法中的参数，也可以是字符串格式如 "%s-%s-%d" 会尝试用$params来调用vsprintf
     */
    public function mkPcLink($ctl, $args=array(), $params=array(), $http=false, $rewrite=true, $ext='.html')
    {

        if(strpos($ctl,':')){
            $a = explode(':',$ctl);
            $ctl = $a[0];
            $act = $a[1];
            $admin= $a[2];
        }else{
            $act = null;
        }

        $link = $this->_parse_rewrite($ctl, $act, $args, $rewrite, $ext);
        if(is_array($params)){
            //$link = vsprintf($link, $params);
            $params = http_build_query($params);
        }else if(!is_string($params)){
            $params = '';
        }
        if(empty($rewrite) || 'ajax' === $http){
            $link = "{$link}";
//            $link = "index.php?{$link}";
        }

        if($params){
            if(strpos($link,'?') === false){
                $link .= '?'.$params;
            }else{
                $link .= '&'.$params;
            }
        }

        $prefix = '';
        if($http){

            $link = $prefix.'/'.$link;
        }

        return $link;
    }





    protected function _parse_rewrite($ctl, $act=null, $args=array(), $rewrite=true, $ext='.html')
    {
        $link = '';
        $link = "{$ctl}";
        $link .= $act ? "-{$act}" : '';
        if(!empty($args)){
            if(is_array($args)){
                $link .= '-'.implode('-', $args);
            }else if(is_string($args)){
                $link .= '-'.trim($args, '-');
                if(strpos($link, '.html')){
                    $ext = '';
                }
            }
        }
        $link .= $ext;
        return $link;
    }

}