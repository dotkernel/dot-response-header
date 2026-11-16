<?php

declare(strict_types=1);

namespace Dot\ResponseHeader\Factory;

use ArrayAccess;
use Dot\ResponseHeader\Exception\InvalidConfigException;
use Dot\ResponseHeader\Middleware\ResponseHeaderMiddleware;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

use function is_array;

final class ResponseHeaderMiddlewareFactory
{
    public const MESSAGE_MISSING_CONFIG         = 'Unable to find config.';
    public const MESSAGE_MISSING_PACKAGE_CONFIG = 'Unable to find dot-response-header config.';

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws InvalidConfigException
     */
    public function __invoke(ContainerInterface $container): ResponseHeaderMiddleware
    {
        if (! $container->has('config')) {
            throw new InvalidConfigException(self::MESSAGE_MISSING_CONFIG);
        }

        $config = $container->get('config');
        if (! is_array($config) && ! $config instanceof ArrayAccess) {
            throw new InvalidConfigException(self::MESSAGE_MISSING_CONFIG);
        }

        $headers = $config['dot_response_headers'] ?? null;
        if (! is_array($headers)) {
            throw new InvalidConfigException(self::MESSAGE_MISSING_PACKAGE_CONFIG);
        }

        return new ResponseHeaderMiddleware($headers);
    }
}
