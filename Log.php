<?php

/**
 * Class Log
 * @method info
 * @method notice
 * @method warn
 * @method error
 * @method trace
 */
class Log
{
    protected static $level_line=0;
    protected static $log_file='/tmp/jcy_rpc.log';

    const TRACE   = 0;
    const INFO    = 1;
    const NOTICE  = 2;
    const WARN    = 3;
    const ERROR   = 4;

    protected static $level_code = array(
        'TRACE' => 0,
        'INFO' => 1,
        'NOTICE' => 2,
        'WARN' => 3,
        'ERROR' => 4,
    );

    protected static $level_str = array(
        'TRACE',
        'INFO',
        'NOTICE',
        'WARN',
        'ERROR',
    );

    static $date_format = '[Y-m-d H:i:s]';

    static function convert($level)
    {
        if (!is_numeric($level))
        {
            $level = self::$level_code[strtoupper($level)];
        }
        return $level;
    }

    function __call($func, $param)
    {
        $this->put($param[0], $func);
    }


    static function setLevel($level)
    {
        self::$level_line = $level;
    }

    static function init(){

        $dir = dirname(self::$log_file);
        if (file_exists($dir))
        {
            if (!is_writeable($dir) && !chmod($dir, 0777))
            {
                throw new \Exception(__CLASS__.": {$dir} unwriteable.");
            }
        }
        elseif (mkdir($dir, 0777, true) === false)
        {
            throw new \Exception(__CLASS__.": mkdir dir {$dir} fail.");
        }
    }
    static function put($msg, $level = self::TRACE)
    {

        $log = self::format($msg, $level);
        if ($log)
        {
            file_put_contents(self::$log_file,$log,FILE_APPEND);
        }

    }

    static function format($msg, $level)
    {
        $level = self::convert($level);
        if ($level < self::$level_line)
        {
            return false;
        }
        $level_str = self::$level_str[$level];
        return date(self::$date_format)."\t{$level_str}\r\n{$msg}\r\n";
    }

    function flush()
    {

    }
}
