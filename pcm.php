<?php

if(exec('whoami')!='root'){
    echo "please use root user to exe the script.";
    exit;
}

require_once(__DIR__ .DIRECTORY_SEPARATOR.'Loader.php');//加载框架

$env_name='';
if(isset($argv[1]))
    $env_name = $argv[1];

\Cfg::setEnvName($env_name);
$server = new \Server\Api();
$server->start();