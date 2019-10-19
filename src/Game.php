<?php declare(strict_types=1);

namespace TicTacTwilio;

final class Game
{
    private $winConditions = [
        ["0x0", "0x1", "0x2"],
        ["1x0", "1x1", "1x2"],
        ["2x0", "2x1", "2x2"],
        ["0x0", "1x0", "2x0"],
        ["0x1", "1x1", "2x1"],
        ["0x2", "1x2", "2x2"],
        ["0x0", "1x1", "2x2"],
        ["0x2", "1x1", "2x0"],
    ];

    public function whosTurnIsIt(array $board): string
    {
        if ($this->isBoardEmpty($board)) {
            return 'X';
        }

        if ($this->countMarks($board, 'X') === $this->countMarks($board, '0')) {
            return 'X';
        }

        return '0';
    }

    private function isBoardEmpty(array $board): bool
    {
        for ($r = 0; $r <= 2; $r++) {
            for ($c = 0; $c <= 2; $c++) {
                if (trim($board[$r][$c]) !== '') {
                    return false;
                }
            }
        }
        return true;
    }

    public function isBoardAWin(array $board): ?string
    {
        foreach ($this->winConditions as $winCondition) {
            if (!is_null($winner = $this->isWin($board, $winCondition))) {
                return $winner;
            }
        }

        return null;
    }

    private function isWin(array $board, $winCondition): ?string
    {
        // if the first cell in this win condition is empty, this can't possibly be a win
        // it also takes 3 blanks out of the equation
        if (trim($this->getBoardValue($board, $winCondition[0])) === '') {
            return null;
        }

        if ($this->getBoardValue($board, $winCondition[0]) === $this->getBoardValue($board, $winCondition[1])
            && $this->getBoardValue($board, $winCondition[0]) === $this->getBoardValue($board, $winCondition[2])) {
            return $this->getBoardValue($board, $winCondition[0]);
        }

        return null;
    }

    private function getBoardValue(array $board, string $coords): string
    {
        $cell = explode('x', $coords);
        return $board[$cell[0]][$cell[1]];
    }

    private function countMarks(array $board, string $mark): int
    {
        $numberOfMarks = 0;
        for ($r = 0; $r <= 2; $r++) {
            for ($c = 0; $c <= 2; $c++) {
                if (trim($board[$r][$c]) === $mark) {
                    $numberOfMarks++;
                }
            }
        }

        return $numberOfMarks;
    }

    private function numberOfMovesPlayed(array $board): int
    {
        return $this->countMarks($board, 'X') + $this->countMarks($board, '0');
    }

    public function isLegalMove(array $currentBoard, array $proposedBoard, string $gameTurn): bool
    {
        // compare current board with submitted board
        // 1. how many cells have changed - TICK
        // 2. have Xs turn to 0s or vice versa
        // 3. if only one cell changed, has it changed to the turn we inferred

        $proposedMovesPlayed = $this->numberOfMovesPlayed($proposedBoard);
        $currentMovesPlayed = $this->numberOfMovesPlayed($currentBoard);
        if ($proposedMovesPlayed - $currentMovesPlayed !== 1) {
           return false;
        }

        return true;
    }
}
