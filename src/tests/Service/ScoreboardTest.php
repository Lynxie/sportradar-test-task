<?php
declare(strict_types=1);

namespace App\Tests\Service;

use App\Exception\ScoreboardException;
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

    #[DataProvider('sameTeamNameProvider')]
    public function testCannotStartMatchWithSameTeam(string $homeTeam, string $awayTeam): void
    {
        $this->expectException(ScoreboardException::class);
        $this->scoreboard->startNewMatch($homeTeam, $awayTeam);
    }

    #[DataProvider('invalidTeamNameProvider')]
    public function testCannotStartMatchWithInvalidTeamName(string $homeTeam, string $awayTeam): void
    {
        // Ensure valid name works
        $this->scoreboard->startNewMatch('Team C', 'Team A');

        $this->expectException(ScoreboardException::class);
        $this->scoreboard->startNewMatch($homeTeam, $awayTeam);
    }

    public function testCannotStartMatchWhenTeamIsAlreadyPlaying(): void
    {
        $this->scoreboard->startNewMatch('Team A', 'Team B');

        $this->expectException(ScoreboardException::class);
        $this->scoreboard->startNewMatch('Team C', 'Team A');
    }

    public function testUpdateScoreSuccessfullyUpdatesMatchScore()
    {
        $match1 = $this->scoreboard->startNewMatch('Team A', 'Team B');
        $match2 = $this->scoreboard->startNewMatch('Côte d’Ivoire', 'Congo, Republic of the');

        $match1updated = $this->scoreboard->updateScore('Team A', 'Team B', 1, 0);
        $match2updated = $this->scoreboard->updateScore('Côte d’Ivoire', 'Congo, Republic of the', 0, 1);

        $this->assertSame($match1, $match1updated);
        $this->assertSame($match2, $match2updated);

        $this->assertEquals(1, $match1->getHomeScore());
        $this->assertEquals(0, $match1->getAwayScore());
        $this->assertEquals(0, $match2->getHomeScore());
        $this->assertEquals(1, $match2->getAwayScore());
    }

    #[DataProvider('nonexistentMatchProvider')]
    public function testUpdateScoreFailsForNonexistentMatch(string $homeTeam, string $awayTeam, string $requestedHomeTeam, string $requestedAwayTeam): void
    {
        $match = $this->scoreboard->startNewMatch($homeTeam, $awayTeam);
        $this->expectException(ScoreboardException::class);
        $this->scoreboard->updateScore($requestedHomeTeam, $requestedAwayTeam, 1, 0);
    }

    public static function newMatchesProvider(): array
    {
        return [
            ['Team A', 'Team B'],
            ['USA', 'Estonia'],
            ['Côte d\'Ivoire', 'São Tomé and Príncipe'],
        ];
    }

    public static function sameTeamNameProvider(): array
    {
        return [
            'Same name' => ['Team A', 'Team A'],
            'Case sensitivity test' => ['TEAm A', 'Team A'],
            'Sanitizing test' => ['team a  ', ' Team A'],
        ];
    }

    public static function invalidTeamNameProvider(): array
    {
        return [
            'Empty home team name ' => ['', 'Team A'],
            'Away team name consists of spaces only' => ['Team C', '   '],
            'Both team names are invalid' => [' ', ''],
        ];
    }

    public static function nonexistentMatchProvider(): array
    {
        return [
            'First team invalid' => ['USA', 'Estonia', 'Latvia', 'Estonia'],
            'Second team invalid' => ['USA', 'Estonia', 'USA', 'Latvia'],
            'Teams switched' => ['USA', 'Estonia', 'Estonia', 'USA'],
            'Both teams invalid' => ['USA', 'Estonia', 'Latvia', 'Croatia'],
        ];
    }

}
