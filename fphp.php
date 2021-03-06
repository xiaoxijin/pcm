<?php

if(exec('whoami')!='root'){
    echo "please use root user to exe the script.";
    exit;
}

require_once(__DIR__ .DIRECTORY_SEPARATOR.'Loader.php');//加载框架


use GetOptionKit\OptionCollection;
use GetOptionKit\OptionParser;
use GetOptionKit\OptionPrinter\ConsoleOptionPrinter;

$specs = new OptionCollection;
$specs->add('e|env?', "set env.name when start the rpc server,default value is product. \n example:-e=test");
$specs->add('s|start', 'start the rpc server');
$specs->add('t|stop', 'stop the rpc server');
$specs->add('r|restart', 'restart the rpc server');
$specs->add('h|help', 'show help document');

$printer = new ConsoleOptionPrinter;
$parser = new OptionParser($specs);
$help_document = "Enabled options: \n".$printer->render($specs);
$envName='';
$current_exe='';
try {

    if($result = $parser->parse($argv))
    {

        if(count($result->keys)==0 && count($result->arguments)==0){
            echo $help_document;
            exit;
        }
        if(isset($result->keys['help'])){
            echo $help_document;
            exit;
        }
        foreach ($result->keys as $key => $spec) {

            if($envName!='')
                break;
            switch (strtolower($key)){
                case "env":
                    $envName = $spec->value;
                    break;
                case "start":
                    $current_exe = $key;
                    break;
                case "stop":
                    $current_exe = $key;
                    break;
                case "restart":
                    $current_exe = $key;
                    break;
                default:
                    echo $help_document;
                    exit;
            }
        }
        if($current_exe=='start' || $current_exe=='restart'){
            \Server\Ctl::$current_exe($envName);
        }elseif($current_exe!='')
            \Server\Ctl::$current_exe();
        else
            echo $help_document;
    }else{
        echo $help_document;
    }

} catch( Exception $e ) {
    echo $e->getMessage();
}
