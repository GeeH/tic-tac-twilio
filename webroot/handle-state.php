<?php declare(strict_types=1);

use TicTacTwilio\Game;

require_once('./../vendor/autoload.php');

$board = json_decode($_POST['board']);
$player = json_decode(($_POST['player']));

$game = new Game();

$client = new Twilio\Rest\Client(
    "ACb877821242bbaedc246328ca0a8c3fc6",
    getenv("TWILIO_TOKEN")
);

$sync = $client->sync;
$service = $sync->services->getContext('IS7667e9f62a39c43cc96a012d18354103');

$state = $service->documents($player->boardUuid);

// add game rules to check it's a valid move
// $currentBoard = pull out state of board currently
// look at the move that wants to be played
// it's X's turn and 0 is trying to play, error (and vice versa)
// 0/X is trying to play on a square that is occupied, error
// whatever other way players try to cheese the games

$boardState = $service->documents($player->boardUuid)
    ->fetch();
$currentBoard = $boardState->data['board'];

$gameTurn = $game->whosTurnIsIt($currentBoard);
if (!$game->isLegalMove($currentBoard, $board, $gameTurn)) {
    throw new \InvalidArgumentException('Stop Cheesing');
}

$winner = $game->isBoardAWin($board);

$state->update([
    'data' => ['board' => $board, 'turn' => $player->mark === 'X' ? '0' : 'X', 'winner' => $winner], // if X set 0 else set X
]);