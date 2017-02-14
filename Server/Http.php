<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/2/13
 * Time: 10:22
 */

namespace Server;



class Http extends \Server\Base
{

    public $host='0.0.0.0';
    public $port='9502';
    function __construct($host,$port)
    {
        $this->server =  new \Swoole\Server($host??$this->host,$port??$this->port);
        $this->server->on('Start',[$this,'onStart']);
        $this->server->on('Connect',[$this,'onConnect']);
        $this->server->on('Receive',[$this,'onReceive']);
//        $this->server->on('Request',[$this,'onRequest']);
    }

    function start(){
        $this->server->start();
    }
    function onStart($server)
    {
        echo "Client:Start.\n";
        // TODO: Implement onStart() method.
    }

    function onShutdown($server)
    {
        // TODO: Implement onShutdown() method.
    }
    function onConnect($server, $client_id, $from_id)
    {
        echo "Client:Connect.\n";
//        var_dump($server);
        // TODO: Implement onConnect() method.
    }
    function onClose($server, $client_id, $from_id)
    {
        echo "Client: Close.\n";
        // TODO: Implement onClose() method.
    }
    function onReceive($server, $client_id, $from_id, $data)
    {
        echo "Client:Receive.\n";
//        var_dump($client_id);
//        var_dump($from_id);
//        var_dump($data);
        $server->send($client_id, 'Swoole: fd:'.$from_id . ';from_id'.$from_id.';data='.$data);
//        $server->send("sadfasdf");
        // TODO: Implement onReceive() method.
    }
    function onRequest($request, $response){
        echo "Client:Request.\n";
        ob_start();
//        var_dump($request->get);
//        var_dump($request->post);
//        var_dump($request->cookie);
//        var_dump($request->files);
//        var_dump($request->header);
//        var_dump($request->server);
        $content = ob_get_contents();
        ob_clean();
        $response->cookie("User", "Swoole");
        $response->header("X-Server", "Swoole");
//        $response->end("sdafasd");
    }
}




