<?php
declare(strict_types=1);

namespace App\Tests\Service;

use App\Model\FootballMatch;
use App\Service\Scoreboard;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ScoreboardTest extends TestCase
{

    private Scoreboard $scoreboard;

    protected function setUp(): void
    {
        $this->scoreboard = new Scoreboard();
    }

    #[DataProvider('newMatchesProvider')]
    public function testStartNewMatch(string $homeTeam, string $awayTeam): void
    {
        $match = $this->scoreboard->startNewMatch($homeTeam, $awayTeam);

        $this->assertEquals($homeTeam, $match->getHomeTeam());
        $this->assertEquals($awayTeam, $match->getAwayTeam());
        $this->assertEquals(0, $match->getHomeScore());
        $this->assertEquals(0, $match->getAwayScore());

        $matches = $this->scoreboard->getActiveMatches();
        $this->assertSame([$match], $matches);
    }

    public function testStartMultipleNewMatchesProvider(): void
    {
        $match1 = $this->scoreboard->startNewMatch('Team A', 'Team B');
        $match2 = $this->scoreboard->startNewMatch('Team C', 'Team D');
        $match3 = $this->scoreboard->startNewMatch('Team E', 'Team F');

        $matches = $this->scoreboard->getActiveMatches();
        $this->assertContainsOnlyInstancesOf(FootballMatch::class, $matches);
        $this->assertSame([$match1, $match2, $match3], $matches);
    }

    public static function newMatchesProvider(): array
    {
        return [
            ['Team A', 'Team B'],
            ['USA', 'Estonia'],
            ['Côte d\'Ivoire', 'São Tomé and Príncipe'],
        ];
    }

}
