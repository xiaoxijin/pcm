<?php
namespace Server\Rpc;

class Parser
{

    static function params($query='',& $return_params='')
    {
        if(!empty($query)){
            parse_str($query,$return_params);
        }
        return $return_params;
    }

}
