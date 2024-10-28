<?php
declare(strict_types=1);


namespace App\Service;

class Clock
{

    public function nowImmutable(): \DateTimeImmutable
    {
        return new \DateTimeImmutable();
    }

}