<?php

namespace TicTacTwilioTest;

use PHPUnit\Framework\TestCase;
use TicTacTwilio\Game;

class GameTest extends TestCase
{
    /** @var Game */
    private $game;

    public function setUp(): void
    {
        $this->game = new Game();
    }

    public function isBoardAWinWithDataDataProvider(): array
    {
        return [
            [
                'board' => [
                    ['X', 'X', 'X'],
                    [' ', ' ', ' '],
                    [' ', ' ', ' '],
                ],
                'expectedResult' => 'X'
            ],
            [
                'board' => [
                    [' ', 'X', 'X'],
                    ['0', '0', '0'],
                    [' ', ' ', ' '],
                ],
                'expectedResult' => '0'
            ],
            [
                'board' => [
                    [' ', 'X', 'X'],
                    [' ', '0', '0'],
                    ['X', 'X', 'X'],
                ],
                'expectedResult' => 'X'
            ],
            [
                'board' => [
                    ['0', 'X', 'X'],
                    ['0', ' ', '0'],
                    ['0', ' ', ' '],
                ],
                'expectedResult' => '0'
            ],
            [
                'board' => [
                    [' ', 'X', 'X'],
                    ['0', 'X', '0'],
                    [' ', 'X', ' '],
                ],
                'expectedResult' => 'X'
            ],
            [
                'board' => [
                    [' ', 'X', '0'],
                    ['0', ' ', '0'],
                    [' ', ' ', '0'],
                ],
                'expectedResult' => '0'
            ],
            [
                'board' => [
                    [' ', 'X', '0'],
                    ['0', '0', ' '],
                    ['0', ' ', ' '],
                ],
                'expectedResult' => '0'
            ],
            [
                'board' => [
                    ['X', ' ', 'X'],
                    ['0', 'X', '0'],
                    [' ', ' ', 'X'],
                ],
                'expectedResult' => 'X'
            ],
            [
                'board' => [
                    [' ', ' ', ' '],
                    [' ', ' ', ' '],
                    [' ', ' ', ' '],
                ],
                'expectedResult' => null,
            ]
        ];
    }

    /**
     * @dataProvider isBoardAWinWithDataDataProvider
     */
    public function testIsBoardAWinWithData(array $board, ?string $expectedResult): void
    {
        self::assertSame($this->game->isBoardAWin($board), $expectedResult);
    }

    public function inferTurnFromBoardDataProvider(): array
    {
        return [
            [
                'board' => [
                    [' ', ' ', ' '],
                    [' ', ' ', ' '],
                    [' ', ' ', ' '],
                ],
                'expectedResult' => 'X'
            ],
            [
                'board' => [
                    [' ', ' ', ' '],
                    [' ', ' ', ' '],
                    ['X', ' ', ' '],
                ],
                'expectedResult' => '0'
            ],
            [
                'board' => [
                    [' ', '0', ' '],
                    [' ', ' ', ' '],
                    ['X', ' ', ' '],
                ],
                'expectedResult' => 'X'
            ],
            [
                'board' => [
                    [' ', '0', ' '],
                    [' ', ' ', ' '],
                    ['X', ' ', 'X'],
                ],
                'expectedResult' => '0'
            ],
        ];
    }

    /**
     * @dataProvider inferTurnFromBoardDataProvider
     */
    public function testWhosTurnItIs(array $board, string $expectedResult): void
    {
        self::assertSame($expectedResult, $this->game->whosTurnIsIt($board));
    }

    public function testIsLegalMoveReturnsFalseWith2MovesPlayed()
    {
        $currentBoard = [
            [' ', '0', ' '],
            [' ', ' ', ' '],
            ['X', ' ', 'X'],
        ];

        $proposedBoard = [
            ['0', '0', '0'],
            [' ', ' ', ' '],
            ['X', ' ', 'X'],
        ];

        self::assertFalse($this->game->isLegalMove($currentBoard, $proposedBoard, '0'));
    }

    public function testIsLegalMoveReturnsTrueWith1MovePlayed()
    {
        $currentBoard = [
            [' ', '0', ' '],
            [' ', ' ', ' '],
            ['X', ' ', 'X'],
        ];

        $proposedBoard = [
            ['0', ' ', '0'],
            [' ', ' ', ' '],
            ['X', ' ', 'X'],
        ];

        self::assertTrue($this->game->isLegalMove($currentBoard, $proposedBoard, '0'));
    }
}
