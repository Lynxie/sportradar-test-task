<?php
declare(strict_types=1);

namespace App\Service;

use App\Exception\ScoreboardException;
use App\Model\FootballMatch;

class Scoreboard
{

    private array $matches = [];

    /**
     * @param string $homeTeam
     * @param string $awayTeam
     * @return FootballMatch - created match object
     * @throws ScoreboardException
     */
    public function startNewMatch(string $homeTeam, string $awayTeam): FootballMatch
    {
        if ($this->getCleanTeamName($homeTeam) === $this->getCleanTeamName($awayTeam)) {
            throw new ScoreboardException('Home team and away team must be different');
        }

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

    private function getCleanTeamName(string $name): string
    {
        return trim(mb_strtolower($name));
    }

}