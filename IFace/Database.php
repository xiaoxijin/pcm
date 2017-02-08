<?php
namespace IFace;
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/1/9
 * Time: 14:57
 * Database Driver接口
 * 数据库驱动类的接口
 * @author Xijin.Xiao
 *
 */
interface Database
{
    function query($sql);
    function connect();
    function close();
    function lastInsertId();
    function getAffectedRows();
    function errno();
    function quote($str);
}