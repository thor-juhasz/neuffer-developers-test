<?php

namespace App\Calculation;


interface Operations
{
    /**
     * Perform calculation
     *
     * @param int $a
     * @param int $b
     * @return int
     */
    public static function calc(int $a, int $b): int;
}
