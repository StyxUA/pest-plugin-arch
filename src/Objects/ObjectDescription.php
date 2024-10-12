<?php

declare(strict_types=1);

namespace Pest\Arch\Objects;

use Pest\Arch\Support\PhpCoreExpressions;
use PhpParser\Node\Expr;
use PhpParser\Node\Name;
use PhpParser\NodeAbstract;
use PHPUnit\Architecture\Asserts\Dependencies\Elements\ObjectUses;
use PHPUnit\Architecture\Services\ServiceContainer;

/**
 * @internal
 */
final class ObjectDescription extends \PHPUnit\Architecture\Elements\ObjectDescription // @phpstan-ignore-line
{
    public ObjectUsesByLines $usesByLines;

    /**
     * {@inheritDoc}
     */
    public static function make(string $path): ?self
    {
        /** @var ObjectDescription|null $description */
        $description = parent::make($path);

        if (! $description instanceof \Pest\Arch\Objects\ObjectDescription) {
            return null;
        }

        $uses = [
            ...$description->uses->getIterator(),
            ...self::retrieveCoreUses($description),
        ];
        $description->uses = new ObjectUses($uses);

        // collect object's uses (including core expressions) with lines
        $usesByLines = [];
        if (count($uses) > 0) {
            foreach ([...PhpCoreExpressions::$ENABLED, Name::class] as $class) {
                $names = ServiceContainer::$nodeFinder->findInstanceOf(
                    $description->stmts,
                    $class,
                );
                $names = array_values(array_filter(array_map(static function (NodeAbstract $node): ?array {
                    $name = null;
                    if ($node instanceof Name) {
                        $nameAsString = $node->toString();
                        if (
                            function_exists($nameAsString)
                            || class_exists($nameAsString)
                            || interface_exists($nameAsString)
                            || trait_exists($nameAsString)
                            || enum_exists($nameAsString)
                        ) {
                            $name = ltrim($node->toCodeString(), '\\');
                        }

                    } elseif ($node instanceof Expr) {
                        $name = PhpCoreExpressions::getName($node);
                    }

                    return is_null($name)
                        ? null
                        : ['name' => $name, 'startLine' => $node->getStartLine(), 'endLine' => $node->getEndLine()];
                }, $names,
                )));
                $usesByLines = [
                    ...$usesByLines,
                    ...$names,
                ];
            }
        }
        $description->usesByLines = new ObjectUsesByLines($usesByLines);

        return $description;
    }

    /**
     * @return array<int, string>
     */
    private static function retrieveCoreUses(ObjectDescription $description): array
    {

        $expressions = [];

        foreach (PhpCoreExpressions::$ENABLED as $expression) {
            $expressions = [
                ...$expressions,
                ...ServiceContainer::$nodeFinder->findInstanceOf(
                    $description->stmts,
                    $expression,
                ),
            ];
        }

        /** @var array<int, Expr> $expressions */
        return array_filter(array_map(fn (Expr $expression): string => PhpCoreExpressions::getName($expression), $expressions));
    }
}
