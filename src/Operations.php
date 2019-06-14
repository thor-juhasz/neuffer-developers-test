<?php


interface Operations
{
    /**
     * Perform calculation
     * @param int $a
     * @param int $b
     * @return int
     */
    public function calc(int $a, int $b): int;
}
