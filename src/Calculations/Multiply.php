<?php

final class Multiply extends Calculate implements Operations
{
    public function calc(int $a, int $b): int
    {
        return $a * $b;
    }
}