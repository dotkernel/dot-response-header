<?php

declare(strict_types=1);

namespace DotTest\ResponseHeader\Fixture;

use Psr\Container\ContainerInterface;

use function array_key_exists;

/**
 * Minimal PSR-11 container over an array of ready-made services.
 */
final readonly class ArrayContainer implements ContainerInterface
{
    /**
     * @param array<string, mixed> $services
     */
    public function __construct(
        private array $services = [],
    ) {
    }

    public function get(string $id): mixed
    {
        if (! $this->has($id)) {
            throw new ServiceNotFoundException('Service not found: ' . $id);
        }

        return $this->services[$id];
    }

    public function has(string $id): bool
    {
        return array_key_exists($id, $this->services);
    }
}
