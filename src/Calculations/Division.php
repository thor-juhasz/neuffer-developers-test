<?php

final class Division extends Calculate implements Operations
{
    public function calc(int $a, int $b): int
    {
        if ($b <= 0) return 0;
        return $a / $b;
    }
}