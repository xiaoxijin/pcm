<?php
namespace Xphp\Pack;

class DoraPacket
{

    public static $ret=array();
    public static function packFormat($msg_flag = "OK", $data = array())
    {
        $msg_flag = $msg_flag??'OK';
        $pack = array(
            "code" => self::$ret[$msg_flag]['code'],
            "msg" => self::$ret[$msg_flag]['msg'],
            "data" => $data,
        );

        return $pack;
    }

    public static function packEncode($data, $type = "tcp")
    {

        if ($type == "tcp") {
            $sendStr = serialize($data);

            //if compress the packet
            if (\Xphp\Server\DoraConst::SW_DATACOMPRESS_FLAG == true) {
                $sendStr = gzencode($sendStr, 4);
            }

            if (\Xphp\Server\DoraConst::SW_DATASIGEN_FLAG == true) {
                $signedcode = pack('N', crc32($sendStr . \Xphp\Server\DoraConst::SW_DATASIGEN_SALT));
                $sendStr = pack('N', strlen($sendStr) + 4) . $signedcode . $sendStr;
            } else {
                $sendStr = pack('N', strlen($sendStr)) . $sendStr;
            }

            return $sendStr;
        } else if ($type == "http") {
            $sendStr = json_encode($data);
            return $sendStr;
        } else {
            return self::packFormat("packet type wrong", 100006);
        }

    }

    public static function packDecode($str)
    {
        $header = substr($str, 0, 4);
        $len = unpack("Nlen", $header);
        $len = $len["len"];

        if (\Xphp\Server\DoraConst::SW_DATASIGEN_FLAG == true) {

            $signedcode = substr($str, 4, 4);
            $result = substr($str, 8);

            //check signed
            if (pack("N", crc32($result . \Xphp\Server\DoraConst::SW_DATASIGEN_SALT)) != $signedcode) {
                return self::packFormat("Signed check error!", 100005);
            }

            $len = $len - 4;

        } else {
            $result = substr($str, 4);
        }
        if ($len != strlen($result)) {
            //结果长度不对
            echo "error length...\n";

            return self::packFormat(self::$ret['OK']['msg'],self::$ret['OK']['code']);
        }
        //if compress the packet
        if (\Xphp\Server\DoraConst::SW_DATACOMPRESS_FLAG == true) {
            $result = gzdecode($result);
        }
        $result = unserialize($result);

        return self::packFormat("OK", 0, $result);
    }
}
