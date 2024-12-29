<?php

require_once __DIR__ . "/../vendor/autoload.php";
require_once __DIR__ . "/helpers/supervisor_helpers.php";
header('Content-Type: application/json');

$username = $_POST['username'];
$usernames = [
    [
        'id' => 'jahid.limon',
        'name' => 'Jahidul Hasan Limon',
        'pic' => 'https://lh3.googleusercontent.com/a/ACg8ocLqSjtRwnCpooqgDZWAyIDUOygLqYQ3E-4S_oW3tns_54nmrwM=s64-p-k-rw-no',
    ],
    [
        'id' => 'imtiaz.amin',
        'name' => 'Imtiaz Amin',
        'pic' => 'https://lh3.googleusercontent.com/a-/ALV-UjXfbUEMSV9nb6gqMzw7DsOv-ca_2S0BOeqXDIodinPHyuntOgE=s64-p-k-rw-no',
    ],
    [
        'id' => 'taiful.islam',
        'name' => 'Md. Taiful Islam',
        'pic' => 'https://lh3.googleusercontent.com/a-/ALV-UjVm83G6bARRFrqvKbbB5gMMCgUQBV3Pdv6P7pj5vXxSfm7AWQzL=s64-p-k-rw-no',
    ],
    [
        'id' => 'tasnim.sami',
        'name' => 'Md.Tasnim Sami Khan',
        'pic' => 'https://lh3.googleusercontent.com/a-/ALV-UjXh5Vk1i4VCUFqk5tzA5wwSFT90n3qOWVALP-6iiJJvowYN6fs=s64-p-k-rw-no',
    ],
    [
        'id' => 'mohammad.fahad',
        'name' => 'Mohammad Fahad',
        'pic' => 'https://lh3.googleusercontent.com/a-/ALV-UjXh5Vk1i4VCUFqk5tzA5wwSFT90n3qOWVALP-6iiJJvowYN6fs=s64-p-k-rw-no',
    ],
    [
        'id' => 'mohammad.yasin',
        'name' => 'Mohammad Yasin',
        'pic' => 'https://lh3.googleusercontent.com/a-/ALV-UjVQg8VEHUg7ftM4yryCHg8zkDJUx_MWEkkfe_2MZ9KiFpFTrxHk=s64-p-k-rw-no',
    ],
    [
        'id' => 'nurul.islam',
        'name' => 'Nurul Islam',
        'pic' => 'https://lh3.googleusercontent.com/a-/ALV-UjWVC4SbqsTZ4v4IL_QpSWgL2SErISUATwpi4k1mvmvplXjiyaJG=s64-p-k-rw-no',
    ],
    [
        'id' => 'tanvir.anzum',
        'name' => 'Tanvir Anzum',
        'pic' => 'https://lh3.googleusercontent.com/a-/ALV-UjVtLOTaWdkWmtpOZs9jQmpQmrC1JkMc9SBS6dEIZCRe0fyb8ks=s64-p-k-rw-no',
    ],
    [
        'id' => 'monoranjan.sutradhar',
        'name' => 'Monoranjan Sutradhar',
        'pic' => 'https://lh3.googleusercontent.com/a-/ALV-UjX-aJ6G9v80k6MXyr-jQvME74w-2aL2guOfqimt7YrzEzJKuMk=s64-p-k-rw-no',
    ],
    [
        'id' => 'mithun.adhikary',
        'name' => 'Mithun Kumar Adhikary',
        'pic' => 'https://lh3.googleusercontent.com/a-/ALV-UjVls0CDxo6NicNFSg1dFoLvuiHAlWC82WnVINZlVsZ9NFrln4v7=s64-p-k-rw-no',
    ],
    [
        'id' => 'asif.aman',
        'name' => 'Asif Aman',
        'pic' => 'https://lh3.googleusercontent.com/a-/ALV-UjWwGz5i3CnrOjimWOvI4819mZE-7BWosDXEhCZEnbAvCDyrn5Y=s64-p-k-rw-no',
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
