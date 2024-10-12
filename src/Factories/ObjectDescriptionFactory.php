<?php

declare(strict_types=1);

namespace Pest\Arch\Factories;

use Pest\Arch\Objects\ObjectDescription;
use Pest\Arch\Objects\VendorObjectDescription;
use Pest\Arch\Support\PhpCoreExpressions;
use PHPUnit\Architecture\Asserts\Dependencies\Elements\ObjectUses;
use PHPUnit\Architecture\Services\ServiceContainer;
use ReflectionClass;
use ReflectionFunction;

/**
 * @internal
 */
final class ObjectDescriptionFactory
{
    /**
     * Whether the Service Container class has been initialized.
     */
    private static bool $serviceContainerInitialized = false;

    /**
     * Makes a new Object Description instance, is possible.
     */
    public static function make(string $filename, bool $onlyUserDefinedUses = true): ?\PHPUnit\Architecture\Elements\ObjectDescription
    {
        self::ensureServiceContainerIsInitialized();

        $path = (string) realpath($filename);

        $isFromVendor = str_contains($path, DIRECTORY_SEPARATOR.'vendor'.DIRECTORY_SEPARATOR);
        $originalErrorReportingLevel = error_reporting();
        error_reporting($originalErrorReportingLevel & ~E_USER_DEPRECATED);

        try {
            $object = $isFromVendor
                ? VendorObjectDescription::make($filename)
                : ObjectDescription::make($filename);

        } finally {
            error_reporting($originalErrorReportingLevel);
        }

        if ($object === null) {
            return null;
        }

        if ($object instanceof ObjectDescription) {
            $object->uses = new ObjectUses(array_values(
                array_filter(
                    iterator_to_array($object->uses->getIterator()),
                    static fn (string $use): bool => (! $onlyUserDefinedUses || self::isUserDefined($use)) && ! self::isSameLayer($object, $use),
                )
            ));
            $ignoredLines = self::getIgnoredLines($path);
            $object->usesByLines->filter(
                static fn (array $use): bool => ! in_array($use['startLine'], $ignoredLines, true)
                    && (! $onlyUserDefinedUses || self::isUserDefined($use['name']))
                    && ! self::isSameLayer($object, $use['name']),
            );
        }

        return $object;
    }

    /**
     * Ensures the Service Container class is initialized.
     */
    private static function ensureServiceContainerIsInitialized(): void
    {
        if (! self::$serviceContainerInitialized) {
            ServiceContainer::init();

            self::$serviceContainerInitialized = true;
        }
    }

    /**
     * Checks if the given use is "user defined".
     */
    private static function isUserDefined(string $use): bool
    {
        if (PhpCoreExpressions::getClass($use) !== null) {
            return false;
        }

        return match (true) {
            enum_exists($use) => (new \ReflectionEnum($use))->isUserDefined(),
            function_exists($use) => (new ReflectionFunction($use))->isUserDefined(),
            class_exists($use) => (new ReflectionClass($use))->isUserDefined(),
            interface_exists($use) => (new ReflectionClass($use))->isUserDefined(),
            // ...

            default => true,
        };
    }

    /**
     * Checks if the given use is in the same layer as the given object.
     */
    private static function isSameLayer(\PHPUnit\Architecture\Elements\ObjectDescription $object, string $use): bool
    {
        return $use === 'self'
            || $use === 'static'
            || $use === 'parent'
            || $object->reflectionClass->getNamespaceName() === $use;
    }

    /**
     * Scans file for ignored lines (@pest-arch-ignore-line and @pest-arch-ignore-next-line)
     *
     * @return int[]
     */
    private static function getIgnoredLines(string $filename): array
    {
        $ignoredLines = [];
        $lines = file($filename);
        if (is_array($lines)) {
            foreach ($lines as $lineNo => $line) {
                if (str_contains($line, '@pest-arch-ignore-line')) {
                    $ignoredLines[] = $lineNo + 1;
                } elseif (str_contains($line, '@pest-arch-ignore-next-line')) {
                    $ignoredLines[] = $lineNo + 2;
                }
            }
        }

        return array_values(array_unique($ignoredLines, SORT_NUMERIC));
    }
}
