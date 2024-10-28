<?php

namespace App\Service\Store;

use App\Model\FootballMatch;

interface StoreInterface
{

    public function storeMatch(FootballMatch $match): void;
    public function deleteMatch(FootballMatch $match): void;
    public function getMatches(): array;

}