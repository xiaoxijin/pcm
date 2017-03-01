<?php

class Packet
{

    public static $ret=[];
    public static $task_type=[];
    public static function packFormat($msg_flag = "OK", $data = true,$force_msg=null)
    {
        $msg_flag = $msg_flag?$msg_flag:'OK';
        $msg = $force_msg?$force_msg:self::$ret[$msg_flag]['msg']??'';
        if(!is_array($data) || !is_object($data)){
            \Log::put("打包格式不正确，不是数组或对象，当前data:".$data."\t msg_flag:".$msg_flag."\t msg:".$msg);
            $data = \Tool::timestamp();
        }

        if(isset(self::$ret[$msg_flag])){
            $pack = array(
                "code" => self::$ret[$msg_flag]['code'],
                "msg" =>$msg,
                "data" => $data,
            );
        }else{
            $pack = array(
                "code" => self::$ret['SYSTEM_EXCEPTION']['code'],
                "msg" => $msg,
                "data" => $data,
            );
        }
        return $pack;
    }

    public static function packEncode($data, $type = "tcp")
    {

        if ($type == "tcp") {
            $sendStr = serialize($data);

            //if compress the packet
            if (self::$task_type['SW_DATACOMPRESS_FLAG'] == true) {
                $sendStr = gzencode($sendStr, 4);
            }

            if (self::$task_type['SW_DATASIGEN_FLAG'] == true) {
                $signedcode = pack('N', crc32($sendStr . self::$task_type['SW_DATASIGEN_SALT']));
                $sendStr = pack('N', strlen($sendStr) + 4) . $signedcode . $sendStr;
            } else {
                $sendStr = pack('N', strlen($sendStr)) . $sendStr;
            }

            return $sendStr;
        } else if ($type == "http") {
            $sendStr = json_encode($data);
            return $sendStr;
        } else {
            return self::packFormat("PACKET_TYPE_ERROR");
        }

    }

    public static function packDecode($str)
    {
        $header = substr($str, 0, 4);
        $len = unpack("Nlen", $header);
        $len = $len["len"];

        if (self::$task_type['SW_DATASIGEN_FLAG'] == true) {

            $signedcode = substr($str, 4, 4);
            $result = substr($str, 8);

            //check signed
            if (pack("N", crc32($result . self::$task_type['SW_DATASIGEN_SALT'])) != $signedcode) {
                return ['type'=>false,'data'=>self::packFormat("SIGNED_CHECK_ERROR")];
            }

            $len = $len - 4;

        } else {
            $result = substr($str, 4);
        }
        if ($len != strlen($result)) {
            //结果长度不对
            return ['type'=>false,'data'=>self::packFormat("ERROR_LENGTH_ERROR")];
        }
        //if compress the packet
        if (self::$task_type['SW_DATACOMPRESS_FLAG'] == true) {
            $result = gzdecode($result);
        }
        $result = unserialize($result);
        return ['type'=>'tcp','data'=>self::packFormat("OK", $result)];
    }
}
