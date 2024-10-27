<?php
declare(strict_types=1);

namespace App\Model;

class FootballMatch
{

    private int $homeScore = 0;
    private int $awayScore = 0;

    public function __construct(
        private readonly string $homeTeam,
        private readonly string $awayTeam,
    )
    {

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

}