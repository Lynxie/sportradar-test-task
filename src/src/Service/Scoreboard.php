<?php
declare(strict_types=1);

namespace App\Service;

use App\Model\FootballMatch;

class Scoreboard
{

    public function startNewMatch(string $homeTeam, string $awayTeam): FootballMatch
    {
        // Start a new match
        return new FootballMatch();
    }

    /**
     * @return array|FootballMatch[]
     */
    public function getActiveMatches(): array
    {
        return [];
    }

}