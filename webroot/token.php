<?php
const SERVICE_SID = 'IS7667e9f62a39c43cc96a012d18354103';
include('../vendor/autoload.php');

use Twilio\Jwt\AccessToken;
use Twilio\Jwt\Grants\SyncGrant;
use Twilio\Rest\Client;

const LIST_SID = 'ESec3d704a95c24d659d6ccb367168cd85';

// Load environment variables from .env, or environment if available
$dotenv = new \Dotenv\Dotenv(__DIR__);
$dotenv->load();

// An identifier for your app - can be anything you'd like
$appName = 'Codename TicTacToe';

$client = new Client(getenv('TWILIO_ACCOUNT_SID'), getenv('TWILIO_TOKEN'));

// choose a random username for the connecting user
$identity = \Ramsey\Uuid\Uuid::uuid4(); // Thank you Ben @ramsey

// Create access token, which we will serialize and send to the client
$token = new AccessToken(
    getenv('TWILIO_ACCOUNT_SID'),
    getenv('TWILIO_API_KEY'),
    getenv('TWILIO_API_SECRET'),
    3600,
    $identity
);

// Grant access to Sync
$syncGrant = new SyncGrant();
if (empty(getenv('TWILIO_SYNC_SERVICE_SID'))) {
    $syncGrant->setServiceSid('default');
} else {
    $syncGrant->setServiceSid(getenv('TWILIO_SYNC_SERVICE_SID'));
}
$token->addGrant($syncGrant);

// find open game or create new game
$sync = $client->sync;
$service = $sync->v1->services->getContext( SERVICE_SID);

$latestBoards = $service->syncLists(LIST_SID)
    ->syncListItems->read(['order' => 'desc'], 1);

// add a player to open game if an open game exists
if (count($latestBoards) === 1 && count($latestBoards[0]->data['players']) === 1) {
    $mark = '0';
    $board = $latestBoards[0]->data;
    $board['players'][] = ['identify' => $identity, 'mark' => $mark];

    // update this board on the list
    $latestBoards[0]->update([
        'data' => $board
    ]);

// no open game exists, create a new game
} else {
    $mark = 'X';
    $board = [
        'boardUuid' => \Ramsey\Uuid\Uuid::uuid4(), // don't forget to thank @ramsey
        'players' => [
            ['identify' => $identity, 'mark' => $mark],
        ],
    ];

    // put board data onto list
    $pushResult = $service->syncLists(LIST_SID)
        ->syncListItems->create($board);

    // create board document
    $createResult = $client->sync->v1->services(getenv('TWILIO_SYNC_SERVICE_SID'))
        ->documents->create([
            'uniqueName' => $board['boardUuid'],
            'data' => ['turn' => $mark],
        ]);
}

// add permission for this board for this user
$client->sync->v1->services(getenv('TWILIO_SYNC_SERVICE_SID'))
    ->documents($board['boardUuid']) // whatever board we are playing
    ->documentPermissions($identity)
    ->update(true, false, false);

// return serialized token and the user's randomly generated ID
header('Content-type:application/json;charset=utf-8');
echo json_encode([
    'identity' => $identity,
    'token' => $token->toJWT(),
    'mark' => $mark,
    'boardUuid' => $board['boardUuid'],
]);