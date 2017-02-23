<?php
/**
 * Date: 2017/1/16
 * Time: 9:32
 */

error_reporting(E_ALL);
//error_reporting(E_ALL ^ E_NOTICE);

require_once(__DIR__.DIRECTORY_SEPARATOR.'Loader.php');//加载框架自动加载类库

$server = new \Server\Api();
$server->start();