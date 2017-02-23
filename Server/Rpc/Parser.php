<?php
namespace Server\Rpc;

class Parser
{

    static function params($query='')
    {
        $query = trim($query);
        if(!empty($query)){
            parse_str($query,$return_params);
            if(count($return_params)==1 && empty($return_params[0])){
                $return_params = $query;
            }
        }
        return $return_params;
    }

}
