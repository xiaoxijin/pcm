<?php
namespace Server;

class System
{
    function kill($pid, $signo)
    {
        return posix_kill($pid, $signo);
    }

    function fork()
    {
        return pcntl_fork();
    }
}