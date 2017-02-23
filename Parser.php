<?php

class Parser
{
    static function actionParams($query='')
    {
        $return_params = '';
        $query = trim($query);
        if(!empty($query) && !is_array($query)){
            parse_str($query,$return_params);
            if(count($return_params)==1 && empty($return_params[0])){
                $return_params = $query;
            }
        }
        return $return_params;
    }

    static function cacheKey($key){
        return \Cache\Remote::decodeKey($key);
    }
}