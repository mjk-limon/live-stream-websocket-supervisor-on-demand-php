<?php

require_once __DIR__ . "/../vendor/autoload.php";
require_once __DIR__ . "/helpers/supervisor_helpers.php";
header('Content-Type: application/json');

$username = $_POST['username'];
$usernames = [
    [
        'id' => 12232,
        'name' => 'জাহিদ লিমন',
        'pic' => 'assets/ppic/12232.jpg',
    ],
    [
        'id' => 12233,
        'name' => 'কনক',
        'pic' => 'assets/ppic/12233.jpg',
    ],
];

$found = array_search($username, array_column($usernames, 'id'));

if ($found !== false) {
    http_response_code(200);
    start_socket_if_not_running();
    exit(json_encode(['exists' => true, 'user' => $usernames[$found]]));
}

http_response_code(404);
exit(json_encode(['exists' => false]));
