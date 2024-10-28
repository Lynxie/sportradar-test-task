<?php
declare(strict_types=1);


namespace App\Service\Store;

use App\Model\FootballMatch;

class ArrayStore implements StoreInterface
{

    private array $matches = [];

    public function storeMatch(FootballMatch $match): void
    {
        $this->matches[] = $match;
    }

    public function deleteMatch(FootballMatch $match): void
    {
        $matchIndex = array_search($match, $this->matches, true);
        if ($matchIndex !== false) {
            unset($this->matches[$matchIndex]);
            $this->matches = array_values($this->matches); // rearranging array
        }
    }

    /**
     * @return array|FootballMatch[]
     */
    public function getMatches(): array
    {
        return $this->matches;
    }

}