<?php

declare(strict_types=1);

namespace Pest\Arch\Expectations;

use Pest\Arch\Blueprint;
use Pest\Arch\Collections\Dependencies;
use Pest\Arch\Exceptions\ArchExpectationFailedException;
use Pest\Arch\Options\LayerOptions;
use Pest\Arch\SingleArchExpectation;
use Pest\Arch\ValueObjects\Targets;
use Pest\Arch\ValueObjects\Violation;
use Pest\Expectation;
use PHPUnit\Framework\ExpectationFailedException;

/**
 * @internal
 */
final class ToUse
{
    /**
     * Creates an "ToUse" expectation.
     *
     * @param  array<int, string>|string  $dependencies
     */
    public static function make(Expectation $expectation, array|string $dependencies): SingleArchExpectation
    {
        assert(is_string($expectation->value) || is_array($expectation->value));

        $blueprint = Blueprint::make(
            Targets::fromExpectation($expectation),
            Dependencies::fromExpectationInput($dependencies),
        );

        return SingleArchExpectation::fromExpectation(
            $expectation,
            static function (LayerOptions $options) use ($blueprint): void {
                $blueprint->expectToUse(
                    $options,
                    static function (string $value, string $dependOn): void {
                        $message = "Expecting '$value' to use '$dependOn'.";
                        throw new ExpectationFailedException($message);
                    },
                );
            },
        );
    }
}
