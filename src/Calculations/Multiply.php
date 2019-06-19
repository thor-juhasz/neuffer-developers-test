<?php

namespace App\Calculations;

use App\Calculation\Operations;


class Multiply implements Operations
{
    public static function calc(int $a, int $b): int
    {
        return $a * $b;
    }
}