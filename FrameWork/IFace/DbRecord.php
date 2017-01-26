<?php
namespace Xphp\IFace;
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/1/9
 * Time: 14:58
 * Database Driver接口
 * 数据库结果集的接口，提供2种接口
 * fetch 获取单条数据
 * fetch 获取全部数据到数组
 * @author Xijin.Xiao
 */
interface DbRecord
{
    function fetch();
    function fetchall();
}