#!/usr/bin/php
<?php



require_once(__DIR__ .DIRECTORY_SEPARATOR.'Loader.php');//加载框架


use GetOptionKit\OptionCollection;
use GetOptionKit\OptionParser;
use GetOptionKit\OptionPrinter\ConsoleOptionPrinter;
//$defaultOptions = array(
//    'i|init' => 'first execute to set up environment in project root document',
//    's|start' => 'start the rpc server',
//    'r|reload' => 'reload the rpc server',
//    't|stop' => 'stop the rpc server',
//    'r|restart' => 'restart the rpc server',
//    'h|help' => 'show help document',
//
//);
$specs = new OptionCollection;

$specs->add('i|init', 'first execute to set up environment in project root document' );
$specs->add('s|start', 'start the rpc server' );
$specs->add('r|reload', 'reload the rpc server');
$specs->add('t|stop', 'stop the rpc server');
$specs->add('r|restart', 'restart the rpc server');
$specs->add('h|help', 'show help document');

$printer = new ConsoleOptionPrinter;

$parser = new OptionParser($specs);
$help_document = "Enabled options: \n".$printer->render($specs);


$cmd = "env";
var_dump($cmd);
exec($cmd,$output, $return_var);
var_dump($output);
var_dump($return_var);
//passthru("FPHP='change name to jack'",$output);
//var_dump($output);
//exec('echo $FPHP;',$output, $return_var);
//var_dump($output);
//var_dump($return_var);
try {

     system("echo \$FPHP",$fphp_dir);
     echo $fphp_dir;
//    if($result = $parser->parse($argv))
//    {
//        if(count($result->keys)==0 && count($result->arguments)==0){
//            echo $help_document;
//        }
//        if(isset($result->keys['help'])){
//            echo $help_document;
//        }
//    }else{
//        echo $help_document;
//    }


//    var_dump($result->arguments);
//    foreach ($result->keys as $key => $spec) {
//        print_r($spec);
//    }
//
//    $opt = $result->keys['init']; // return the option object.
//    $str = $result->keys['init']->value; // return the option value
//
//    print_r($opt);
//    var_dump($str);

} catch( Exception $e ) {
    echo $e->getMessage();
}


//$classLoader = new \GetOptionKit\SplClassLoader(array('GetOptionKit' => 'src' ));
//$classLoader->register();