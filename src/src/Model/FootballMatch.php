<?php
declare(strict_types=1);

namespace App\Model;

use App\Exception\ScoreboardException;

class FootballMatch
{

    private int $homeScore = 0;
    private int $awayScore = 0;

    private readonly string $homeTeam;
    private readonly string $awayTeam;

    public function __construct(
        string $homeTeam,
        string $awayTeam,
    )
    {
        $homeTeam = $this->sanitizeTeamName($homeTeam);
        $awayTeam = $this->sanitizeTeamName($awayTeam);

        if (empty($homeTeam)) {
            throw new ScoreboardException('Home team name is empty');
        }

        if (empty($awayTeam)) {
            throw new ScoreboardException('Away team name is empty');
        }

        $this->homeTeam = $homeTeam;
        $this->awayTeam = $awayTeam;
    }

    public function getHomeTeam(): string
    {
        return $this->homeTeam;
    }

    public function getAwayTeam(): string
    {
        return $this->awayTeam;
    }

    public function getHomeScore(): int
    {
        return $this->homeScore;
    }

    public function getAwayScore(): int
    {
        return $this->awayScore;
    }

    public function getMatchStartDate(): \DateTimeImmutable
    {

    }

    private function sanitizeTeamName(string $teamName): string
    {
        return trim($teamName);
    }

}