<?php

namespace Tests\Fixtures\Misc;

class NativeFuncsIgnored
{
    public function startSleeping(): void
    {
        sleep(1); // @pest-arch-ignore-line
    }

    public function dieWithStatus(int $status): void
    {
        exit($status); // @pest-arch-ignore-line
    }

    public function exitWithStatus(int $status): void
    {
        exit($status); // @pest-arch-ignore-line
    }

    public function evaluateCode(string $code): void
    {
        eval($code); // @pest-arch-ignore-line
    }

    public function makeAClone(object $object): object
    {
        return clone $object; // @pest-arch-ignore-line
    }

    public function checkArray(array $array): bool
    {
        // @pest-arch-ignore-next-line
        if (empty($array)) {
            return false;
        }

        // @pest-arch-ignore-next-line
        if (! isset($array[1])) {
            return false;
        }

        return true;
    }

    public function printSomething(string $text): void
    {
        // @pest-arch-ignore-next-line
        echo $text;
    }
}
