<?php

namespace Tests\Fixtures\Misc;

class SomeNativeIgnored
{
    public function startSleeping(): void
    {
        sleep(1); // @pest-arch-ignore-line
    }

    public function startSleepingAgain(): void
    {
        sleep(1);
    }

    public function dieWithStatus(int $status): void
    {
        exit($status); // @pest-arch-ignore-line
    }

    public function exitWithStatus(int $status): void
    {
        // @pest-arch-ignore-next-line
        exit($status);
    }
}
