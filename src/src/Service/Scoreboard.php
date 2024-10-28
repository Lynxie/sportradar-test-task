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

        if ($this->findTeamMatch($homeTeam) !== null) {
            throw new ScoreboardException('Home team is currently in another match');
        }

        if ($this->findTeamMatch($awayTeam) !== null) {
            throw new ScoreboardException('Away team is currently in another match');
        }

        $match = new FootballMatch($homeTeam, $awayTeam);
        $this->matches[] = $match;

        return $match;
    }

    public function updateScore(string $homeTeam, string $awayTeam, int $homeScore, int $awayScore): FootballMatch
    {
        $match = $this->findMatchForTeams($homeTeam, $awayTeam);
        if ($match === null) {
            throw new ScoreboardException('Match not found with the specified teams');
        }
        $match->updateScore($homeScore, $awayScore);

        return $match;
    }

    public function finishMatch(string $homeTeam, string $awayTeam): FootballMatch
    {
        $match = $this->findMatchForTeams($homeTeam, $awayTeam);
        $this->deleteMatch($match);

        return $match;
    }

    /**
     * @return array|FootballMatch[]
     */
    public function getActiveMatches(): array
    {
        return $this->matches;
    }

    private function findMatchForTeams(string $homeTeam, string $awayTeam): ?FootballMatch
    {
        $cleanHomeTeam = $this->getCleanTeamName($homeTeam);
        $cleanAwayTeam = $this->getCleanTeamName($awayTeam);

        foreach ($this->getActiveMatches() as $activeMatch) {
            if (
                $this->getCleanTeamName($activeMatch->getHomeTeam()) === $cleanHomeTeam &&
                $this->getCleanTeamName($activeMatch->getAwayTeam()) === $cleanAwayTeam
            ) {
                return $activeMatch;
            }
        }

        return null;
    }

    private function findTeamMatch(string $teamName): ?FootballMatch
    {
        $cleanTeamName = $this->getCleanTeamName($teamName);
        foreach ($this->getActiveMatches() as $activeMatch) {
            if (
                $this->getCleanTeamName($activeMatch->getHomeTeam()) === $cleanTeamName ||
                $this->getCleanTeamName($activeMatch->getAwayTeam()) === $cleanTeamName
            ) {
                return $activeMatch;
            }
        }

        return null;
    }

    private function deleteMatch(FootballMatch $match): void
    {
        $matchIndex = array_search($match, $this->matches, true);
        if ($matchIndex !== false) {
            unset($this->matches[$matchIndex]);
            $this->matches = array_values($this->matches); // rearranging array
        }
    }

    private function getCleanTeamName(string $name): string
    {
        return trim(mb_strtolower($name));
    }

}