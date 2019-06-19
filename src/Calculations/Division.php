<?php

namespace App\Calculations;

use App\Calculation\Operations;


class Division implements Operations
{
    public static function calc(int $a, int $b): int
    {
        if ($b === 0) {
            return 0;
        }
        return $a / $b;
    }
}