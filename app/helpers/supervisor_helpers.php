<?php

define('SUPERVISOR_RPC', "http://127.0.0.1:9091/RPC2");
define('SUPERVISOR_USER', "user");
define('SUPERVISOR_PASS', "pass");

function get_supervisor_rpc()
{
    $client = new fXmlRpc\Client(
        SUPERVISOR_RPC,
        new fXmlRpc\Transport\PsrTransport(
            new GuzzleHttp\Psr7\HttpFactory(),
            new \GuzzleHttp\Client([
                'auth' => [SUPERVISOR_USER, SUPERVISOR_PASS],
            ])
        )
    );

    return new Supervisor\Supervisor($client);
}

function start_socket()
{
    $supervisor = get_supervisor_rpc();
    $supervisor->startProcess('chat_socket', false);
}

function check_socket()
{
   $supervisor = get_supervisor_rpc();
   return $supervisor->getProcess('chat_socket')->isRunning();
}

function start_socket_if_not_running()
{
    if (!check_socket()) {
        start_socket();
    }
}
