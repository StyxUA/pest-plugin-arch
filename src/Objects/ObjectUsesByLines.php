<?php

declare(strict_types=1);

namespace Pest\Arch\Objects;

use ArrayIterator;
use IteratorAggregate;
use Traversable;

/**
 * @implements IteratorAggregate<array{name: string, startLine: int, endLine: int}>
 */
final class ObjectUsesByLines implements IteratorAggregate
{
    /**
     * @param  array<int, array{name: string, startLine: int, endLine: int}>  $uses
     */
    public function __construct(protected array $uses) {}

    #[\ReturnTypeWillChange]
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->uses);
    }

    public function filter(callable $callback): self
    {
        $this->uses = array_values(array_filter($this->uses, $callback));
        return $this;
    }
}
