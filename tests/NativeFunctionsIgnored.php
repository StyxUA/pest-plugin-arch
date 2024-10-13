<?php

use PHPUnit\Framework\ExpectationFailedException;
use Tests\Fixtures\Misc\NativeFuncsIgnored;
use Tests\Fixtures\Misc\SomeNativeIgnored;

test('native functions usage with ignore comments 1', function () {
    expect(NativeFuncsIgnored::class)->not->toUse('sleep')
        ->and(NativeFuncsIgnored::class)->not->toUse('die')
        ->and('sleep')->not->toBeUsedIn(NativeFuncsIgnored::class)
        ->and('die')->not->toBeUsedIn(NativeFuncsIgnored::class);
});

test('native functions usage with ignore comments 2', function (string $function) {
    expect($function)->not->toBeUsedIn('Tests\Fixtures\Misc\NativeFuncsIgnored');
})->with(['sleep', 'die', 'eval', 'exit', 'clone', 'empty', 'isset', 'print']);

test('native functions usage with partial ignore comments 1', function () {
    expect(SomeNativeIgnored::class)->not->toUse('sleep');
})->throws(
    ExpectationFailedException::class,
    "Expecting 'Tests\Fixtures\Misc\SomeNativeIgnored' not to use 'sleep'."
);

test('native functions usage with partial ignore comments 2', function () {
    expect('sleep')->not->toBeUsedIn(SomeNativeIgnored::class);
})->throws(
    ExpectationFailedException::class,
    "Expecting 'sleep' not to be used in 'Tests\Fixtures\Misc\SomeNativeIgnored'.",
);
