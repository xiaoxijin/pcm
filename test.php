<?php

require_once(__DIR__ .DIRECTORY_SEPARATOR.'Loader.php');//加载框架


\Cfg::setEnvName();
$task='gym/banner/count';
var_dump(service($task));
exit;