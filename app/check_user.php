<?php

header('Content-Type: application/json');

$username = $_POST['username'];
$usernames = [
    [
        'id' => 12232,
        'name' => 'জাহিদ লিমন',
        'pic' => 'https://via.placeholder.com/50',
    ],
    [
        'id' => 12233,
        'name' => 'কনক',
        'pic' => 'https://via.placeholder.com/50',
    ],
];

$found = array_search($username, array_column($usernames, 'id'));

if ($found !== false) {
    http_response_code(200);
    exit(json_encode(['exists' => true, 'user' => $usernames[$found]]));
}


http_response_code(404);
exit(json_encode(['exists' => false]));
