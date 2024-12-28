<?php

require_once __DIR__ . "/helpers/socket_helpers.php";
set_time_limit(0);
ob_implicit_flush();

$address = '0.0.0.0';
$port = 9000;

$clients = [];
$last_activity = time();

$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
socket_set_option($socket, SOL_SOCKET, SO_REUSEADDR, 1);
socket_bind($socket, $address, $port);
socket_listen($socket);

while (true) {
    $read = array_merge([$socket], $clients);
    $write = null;
    $except = null;

    $num_changed_sockets = socket_select($read, $write, $except, 1);

    if ($num_changed_sockets === false) {
        break;
    }

    if ($num_changed_sockets > 0) {
        if (in_array($socket, $read)) {
            $new_socket = socket_accept($socket);
            $header = socket_read($new_socket, 1024);
            perform_handshaking($header, $new_socket, $address, $port);

            $clients[] = $new_socket;
            $last_activity = time();
        }

        foreach ($read as $changed_socket) {
            if ($changed_socket !== $socket) {
                while (socket_recv($changed_socket, $buf, 1024, 0) >= 1) {
                    $received_text = unmask($buf);
                    $tst_msg = json_decode($received_text, true);

                    if ($tst_msg !== null) {
                        $msgtype = $tst_msg['type'];
                        $user_pic = $tst_msg['pic'];
                        $user_name = $tst_msg['name'];
                        $user_message = $tst_msg['message'];

                        //prepare data to be sent to client
                        $response_text = mask(json_encode(array('type' => $msgtype, 'name' => $user_name, 'message' => $user_message, 'pic' => $user_pic)));
                        send_message($response_text);
                    }

                    $last_activity = time();
                    break 2;
                }

                $buf = @socket_read($changed_socket, 1024, PHP_NORMAL_READ);
                if ($buf === false) {
                    $found_socket = array_search($changed_socket, $clients);
                    unset($clients[$found_socket]);
                }
            }
        }
    }

    if (time() - $last_activity > 30) {
        break;
    }
}

socket_close($socket);
exit(0);
