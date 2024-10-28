<?php
declare(strict_types=1);

namespace App\Tests\Service;

use App\Exception\ScoreboardException;
use App\Model\FootballMatch;
use App\Service\Clock;
use App\Service\Scoreboard;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ScoreboardTest extends TestCase
{

    private Scoreboard $scoreboard;

    protected function setUp(): void
    {
        $clock = new Clock();
        $this->scoreboard = new Scoreboard($clock);
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

    #[DataProvider('negativeScoreValuesProvider')]
    public function testScoreCannotBeNegative(int $homeScore, int $awayScore): void
    {
        $this->scoreboard->startNewMatch('Team A', 'Team B');
        $this->expectException(ScoreboardException::class);
        $this->scoreboard->updateScore('Team A', 'Team B', $homeScore, $awayScore);
    }

    public function testScoreCannotBeReduced(): void
    {
        // This check depends on business logic and may be redundant,
        // as scores could be adjusted downward in cases of errors, goal cancellations, etc.
        $this->scoreboard->startNewMatch('Team A', 'Team B');
        $this->scoreboard->updateScore('Team A', 'Team B', 1, 0);
        $this->scoreboard->updateScore('Team A', 'Team B', 2, 0);
        $this->expectException(ScoreboardException::class);
        $this->scoreboard->updateScore('Team A', 'Team B', 1, 0);
    }

    #[DataProvider('differentlyFormattedTeamNames')]
    public function testCorrectMatchFinishesSuccessfully(string $homeTeam, string $awayTeam, string $formattedHomeTeam, string $formattedAwayTeam): void
    {
        $fillerMatch1 = $this->scoreboard->startNewMatch('Team A', 'Team B');
        $match = $this->scoreboard->startNewMatch($homeTeam, $awayTeam);
        $fillerMatch2 = $this->scoreboard->startNewMatch('Team C', 'Team D');
        $this->assertCount(3, $this->scoreboard->getActiveMatches());

        $finishedMatch = $this->scoreboard->finishMatch($formattedHomeTeam, $formattedAwayTeam);
        $this->assertSame($match, $finishedMatch);
        $this->assertSame([$fillerMatch1, $fillerMatch2], $this->scoreboard->getActiveMatches());
    }

    public function testCannotFinishNonexistentMatch(): void
    {
        $this->expectException(ScoreboardException::class);
        $this->scoreboard->finishMatch('USA', 'Estonia');
    }

    public function testCannotFinishNonexistentMatchInList(): void
    {
        $this->scoreboard->startNewMatch('Team A', 'Team B');
        $this->scoreboard->startNewMatch('Team C', 'Team D');
        $this->expectException(ScoreboardException::class);
        $this->scoreboard->finishMatch('USA', 'Estonia');
    }

    #[DataProvider('summarySortingDatasetProvider')]
    public function testGetSummaryOfMatches(array $matches, array $expectedOrder): void
    {
        $clock = $this->createMock(Clock::class);
        $scoreboard = new Scoreboard($clock);
        $times = array_map(fn($matchArray) => $matchArray[4], $matches);
        $clock->method('nowImmutable')->willReturnOnConsecutiveCalls(...$times);

        foreach ($matches as $matchArray) {
            $scoreboard->startNewMatch($matchArray[0], $matchArray[1]);
            $scoreboard->updateScore($matchArray[0], $matchArray[1], $matchArray[2], $matchArray[3]);
        }

        $actualOrder = array_map(function (FootballMatch $match) {
            return [$match->getHomeTeam(), $match->getAwayTeam()];
        }, $scoreboard->getMatchesSummary());

        $this->assertEquals($expectedOrder, $actualOrder);
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

    public static function negativeScoreValuesProvider(): array
    {
        return [
            [-2, 5],
            [1, -6],
            [-100, -1],
        ];
    }

    public static function differentlyFormattedTeamNames(): array
    {
        return [
            'Same names' => ['USA', 'Estonia', 'USA', 'Estonia'],
            'Different case names' => ['USA', 'Estonia', 'usa', 'estonia'],
            'Extra spaces and invalid case' => ['USA', 'Estonia', '  usa', ' esTONIA   '],
        ];
    }

    public static function summarySortingDatasetProvider(): array
    {
        return [
            'Empty dataset' => [
                'matches' => [],
                'expectedOrder' => [],
            ],
            'Sort by score test' => [
                'matches' => [
                    ['Team A', 'Team B', 1, 0, new \DateTimeImmutable('2010-01-28T15:00:00+02:00')],
                    ['Team C', 'Team D', 3, 0, new \DateTimeImmutable('2010-01-28T15:00:00+02:00')],
                    ['Team E', 'Team F', 0, 2, new \DateTimeImmutable('2010-01-28T15:00:00+02:00')],
                ],
                'expectedOrder' => [
                    ['Team C', 'Team D'], ['Team E', 'Team F'], ['Team A', 'Team B'],
                ]
            ],
            'Sort by time test' => [
                'matches' => [
                    ['Team A', 'Team B', 5, 0, new \DateTimeImmutable('2010-01-28T12:00:00+02:00')],
                    ['Team C', 'Team D', 3, 2, new \DateTimeImmutable('2010-01-28T10:00:00+02:00')],
                    ['Team E', 'Team F', 0, 5, new \DateTimeImmutable('2010-01-28T15:00:00+02:00')],
                ],
                'expectedOrder' => [
                    ['Team E', 'Team F'], ['Team A', 'Team B'], ['Team C', 'Team D'],
                ]
            ],
            'Sort by time in different timezones' => [
                'matches' => [
                    ['Team A', 'Team B', 1, 1, new \DateTimeImmutable('2010-01-28T10:00:00+03:00')],
                    ['Team C', 'Team D', 2, 0, new \DateTimeImmutable('2010-01-28T10:00:00+02:00')],
                    ['Team E', 'Team F', 0, 2, new \DateTimeImmutable('2010-01-28T10:00:00+04:00')],
                ],
                'expectedOrder' => [
                    ['Team C', 'Team D'], ['Team A', 'Team B'], ['Team E', 'Team F'],
                ]
            ],
            'Task dataset' => [
                'matches' => [
                    ['Mexico', 'Canada', 0, 5, new \DateTimeImmutable('2010-01-28T10:00:00+02:00')],
                    ['Spain', 'Brazil', 10, 2, new \DateTimeImmutable('2010-01-28T11:00:00+02:00')],
                    ['Germany', 'France', 2, 2, new \DateTimeImmutable('2010-01-28T12:00:00+02:00')],
                    ['Uruguay', 'Italy', 6, 6, new \DateTimeImmutable('2010-01-28T13:00:00+02:00')],
                    ['Argentina', 'Australia', 3, 1, new \DateTimeImmutable('2010-01-28T14:00:00+02:00')],
                ],
                'expectedOrder' => [
                    ['Uruguay', 'Italy'], ['Spain', 'Brazil'], ['Mexico', 'Canada'],
                    ['Argentina', 'Australia'], ['Germany', 'France'],
                ]
            ],
        ];
    }

}
