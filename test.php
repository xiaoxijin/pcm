<?php

require_once(__DIR__ .DIRECTORY_SEPARATOR.'Loader.php');//加载框架


\Cfg::setEnvName();
var_dump(\Cfg::get('db'));
$task='ucenter/member/list';
var_dump(service($task));
exit;