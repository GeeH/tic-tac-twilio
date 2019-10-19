<?php declare(strict_types=1);

require_once('./../vendor/autoload.php');

$client = new Twilio\Rest\Client(
    "ACb877821242bbaedc246328ca0a8c3fc6",
    getenv("TWILIO_TOKEN")
);

$sync = $client->sync;
$service = $sync->services->getContext('IS7667e9f62a39c43cc96a012d18354103');

$state = $service->documents("SyncGame");

$board = [
    ["", "", ""],
    ["", "", ""],
    ["", "", ""],
];


//$state->update([
//    'data' => ["board" => $board],
//]);

$list = $service->syncLists->create();
var_dump($list);