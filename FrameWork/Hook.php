<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/1/26
 * Time: 15:57
 */

namespace Xphp;


class Hook
{

    static private $hooks;
    /**
     * 执行Hook函数列表
     * @param $type
     */
    static public function call($type)
    {
        if (isset(self::$hooks[$type]))
        {
            foreach (self::$hooks[$type] as $f)
            {
                if (!is_callable($f))
                {
                    trigger_error("Xphp Framework: hook function[$f] is not callable.");
                    continue;
                }
                $f();
            }
        }
    }

    /**
     * 增加钩子函数
     * @param $type
     * @param $func
     * @param $prepend bool
     */
    static public function add($type, $func, $prepend = false)
    {
        if ($prepend)
        {
            array_unshift(self::$hooks[$type], $func);
        }
        else
        {
            self::$hooks[$type][] = $func;
        }
    }

    /**
     * 清理钩子程序
     * @param $type
     */
    static public function clear($type = 0)
    {
        if ($type == 0)
        {
            self::$hooks = array();
        }
        else
        {
            self::$hooks[$type] = array();
        }
    }

}