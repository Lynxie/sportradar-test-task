<?php
declare(strict_types=1);

namespace App\Service;

use App\Model\FootballMatch;

class Scoreboard
{

    private array $matches = [];

    public function startNewMatch(string $homeTeam, string $awayTeam): FootballMatch
    {
        // Start a new match
        $match = new FootballMatch($homeTeam, $awayTeam);
        $this->matches[] = $match;

        return $match;
    }

    /**
     * @return array|FootballMatch[]
     */
    public function getActiveMatches(): array
    {
        return $this->matches;
    }

}